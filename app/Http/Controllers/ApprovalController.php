<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\FormApproval;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    /**
     * Display a listing of requests awaiting the current user's approval.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Base query for requests
        $baseQuery = FormRequest::with(['requester.department', 'fromDepartment', 'toDepartment', 'iomDetails', 'leaveDetails'])
            ->where(function($query) use ($user) {
                // Get requests where user is current approver
                $query->where('current_approver_id', $user->accnt_id)
                    ->orWhere(function($q) use ($user) {
                        // Get requests from user's department that need department head approval
                        $q->whereNull('current_approver_id')
                          ->where('status', 'Pending')
                          ->whereHas('requester', function($q2) use ($user) {
                              $q2->where('department_id', $user->department_id);
                          });
                    });
            });

        // Apply filters
        if ($request->filled('type')) {
            $baseQuery->where('form_type', $request->type);
        }

        if ($request->filled('date_range')) {
            $now = Carbon::now();
            switch ($request->date_range) {
                case 'today':
                    $baseQuery->whereDate('date_submitted', $now->toDateString());
                    break;
                case 'week':
                    $baseQuery->whereBetween('date_submitted', [
                        $now->startOfWeek()->toDateTimeString(),
                        $now->endOfWeek()->toDateTimeString()
                    ]);
                    break;
                case 'month':
                    $baseQuery->whereMonth('date_submitted', $now->month)
                             ->whereYear('date_submitted', $now->year);
                    break;
            }
        }

        if ($request->filled('priority')) {
            $baseQuery->where('priority', $request->priority);
        }

        // Get pending requests for statistics
        $pendingRequests = (clone $baseQuery)
            ->whereIn('status', ['Pending', 'In Progress', 'Pending Department Head Approval', 'Pending Target Department Approval']);

        // Calculate statistics
        $stats = [
            'pending' => $pendingRequests->count(),
            'today' => (clone $baseQuery)->whereDate('date_submitted', Carbon::today())->count(),
            'overdue' => (clone $pendingRequests)->where('date_submitted', '<', Carbon::now()->subDays(2))->count()
        ];

        // Calculate average processing time
        $completedRequests = FormRequest::where(function($query) use ($user) {
                $query->where('current_approver_id', $user->accnt_id)
                    ->orWhere(function($q) use ($user) {
                        $q->whereHas('requester', function($q2) use ($user) {
                            $q2->where('department_id', $user->department_id);
                        });
                    });
            })
            ->where('status', 'Approved')
            ->with(['approvals' => function($query) {
                $query->whereIn('action', ['Approved', 'Submitted']);
            }])
            ->get();

        $totalProcessingTime = 0;
        $completedCount = 0;

        foreach ($completedRequests as $req) {
            $submitted = $req->approvals->where('action', 'Submitted')->first();
            $approved = $req->approvals->where('action', 'Approved')->first();
            
            if ($submitted && $approved) {
                $totalProcessingTime += $submitted->action_date->diffInHours($approved->action_date);
                $completedCount++;
            }
        }

        $stats['avgTime'] = $completedCount > 0 
            ? round($totalProcessingTime / $completedCount) . 'h'
            : 'N/A';

        // Calculate approval rate
        $totalFinalized = FormRequest::where(function($query) use ($user) {
                $query->where('current_approver_id', $user->accnt_id)
                    ->orWhere(function($q) use ($user) {
                        $q->whereHas('requester', function($q2) use ($user) {
                            $q2->where('department_id', $user->department_id);
                        });
                    });
            })
            ->whereIn('status', ['Approved', 'Rejected'])
            ->count();

        $totalApproved = FormRequest::where(function($query) use ($user) {
                $query->where('current_approver_id', $user->accnt_id)
                    ->orWhere(function($q) use ($user) {
                        $q->whereHas('requester', function($q2) use ($user) {
                            $q2->where('department_id', $user->department_id);
                        });
                    });
            })
            ->where('status', 'Approved')
            ->count();

        $approvalRate = $totalFinalized > 0 
            ? round(($totalApproved / $totalFinalized) * 100)
            : 0;

        // Get the final paginated results
        $requestsToApprove = $baseQuery
            ->latest('date_submitted')
            ->paginate(10)
            ->through(function ($request) {
                // Calculate wait time
                $request->wait_time = $request->date_submitted->diffForHumans(['parts' => 1]);
                $request->is_overdue = $request->date_submitted->diffInDays(now()) > 2;
                return $request;
            });

        // Debug information
        Log::info('Approvals Query Info', [
            'user_id' => $user->accnt_id,
            'filters' => $request->only(['type', 'date_range', 'priority']),
            'total_pending' => $stats['pending'],
            'filtered_requests' => $requestsToApprove->total(),
        ]);

        return view('approvals.index', [
            'requestsToApprove' => $requestsToApprove,
            'stats' => $stats,
            'approvalRate' => $approvalRate,
            'averageResponseTime' => $stats['avgTime'],
        ]);
    }

    /**
     * Process batch approval/rejection of requests
     */
    public function batchAction(Request $request): RedirectResponse
    {
        $request->validate([
            'selected_requests' => 'required|array',
            'selected_requests.*' => 'exists:form_requests,form_id',
            'action' => 'required|in:approve,reject',
            'comment' => 'nullable|string'
        ]);

        $user = Auth::user();
        $action = ucfirst($request->action) . 'd'; // 'Approved' or 'Rejected'
        $successCount = 0;
        $errorCount = 0;

        DB::beginTransaction();
        try {
            foreach ($request->selected_requests as $formId) {
                $formRequest = FormRequest::find($formId);
                
                // Verify user has permission to act on this request
                if ($formRequest->current_approver_id !== $user->accnt_id &&
                    !($user->position === 'Head' && 
                      $user->department_id === $formRequest->requester->department_id && 
                      $formRequest->status === 'Pending')) {
                    $errorCount++;
                    continue;
                }

                // Create approval record
                FormApproval::create([
                    'form_id' => $formId,
                    'approver_id' => $user->accnt_id,
                    'action' => $action,
                    'action_date' => now(),
                    'comments' => $request->comment
                ]);

                // Update request status
                $formRequest->update([
                    'status' => $action,
                    'current_approver_id' => null
                ]);

                $successCount++;
            }

            DB::commit();

            $message = "Successfully {$request->action}d {$successCount} requests.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} requests could not be processed due to permissions.";
            }

            return redirect()->route('approvals.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch approval error: ' . $e->getMessage());
            return redirect()->route('approvals.index')->with('error', 'An error occurred while processing the batch action.');
        }
    }

    /**
     * Display the specified resource for approval.
     */
    public function show(FormRequest $formRequest): View
    {
        $user = Auth::user();
        
        // Allow access if:
        // 1. User is the current_approver_id OR
        // 2. User is department head and request is from their department pending head approval
        $canAccess = $user->accnt_id === $formRequest->current_approver_id ||
                    ($user->position === 'Head' && 
                     $user->department_id === $formRequest->requester->department_id && 
                     ($formRequest->status === 'Pending' || $formRequest->status === 'Pending Department Head Approval'));

        if (!$canAccess && !in_array($formRequest->status, ['Approved', 'Rejected', 'Cancelled', 'Noted'])) {
            abort(403, 'This request is not currently assigned to you or is in a non-actionable state for you.');
        }

        $formRequest->load(['requester.department', 'fromDepartment', 'toDepartment', 'iomDetails', 'leaveDetails', 'approvals.approver']);

        return view('approvals.show', compact('formRequest'));
    }

    public function note(Request $request, FormRequest $formRequest): RedirectResponse
    {
        // Comments are optional for noting
        return $this->processApprovalAction($request, $formRequest, 'Noted');
    }

    public function approve(Request $request, FormRequest $formRequest): RedirectResponse
    {
        // Comments are optional for approval
        return $this->processApprovalAction($request, $formRequest, 'Approved');
    }

    public function reject(Request $request, FormRequest $formRequest): RedirectResponse
    {
        // Validate that comments are provided for rejection
        $request->validate([
            'comments' => 'required|string|min:5|max:1000'
        ], [
            'comments.required' => 'A reason for rejection is required.',
            'comments.min' => 'The rejection reason must be at least 5 characters.',
        ]);

        return $this->processApprovalAction($request, $formRequest, 'Rejected');
    }

    private function processApprovalAction(Request $request, FormRequest $formRequest, string $action): RedirectResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user can access this request
            $canAccess = $user->accnt_id === $formRequest->current_approver_id ||
                        ($user->position === 'Head' && 
                         $user->department_id === $formRequest->requester->department_id && 
                         $formRequest->status === 'Pending');

            if (!$canAccess) {
                return redirect()->back()->with('error', 'You are not authorized to perform this action.');
            }

            // Validate signature data
            $request->validate([
                'name' => 'required|string|max:255',
                'signature' => 'required|string',
                'comments' => $action === 'Rejected' ? 'required|string|min:5|max:1000' : 'nullable|string|max:1000'
            ], [
                'name.required' => 'Please provide your full name for the signature.',
                'signature.required' => 'Digital signature is required.',
                'comments.required' => 'A reason for rejection is required.',
                'comments.min' => 'The rejection reason must be at least 5 characters.'
            ]);

            DB::beginTransaction();

            // Create approval record with signature
            FormApproval::create([
                'form_id' => $formRequest->form_id,
                'approver_id' => $user->accnt_id,
                'action' => $action,
                'comments' => $request->input('comments'),
                'action_date' => now(),
                'signature_name' => $request->input('name'),
                'signature_data' => $request->input('signature')
            ]);

            // Handle different actions
            switch ($action) {
                case 'Rejected':
                    $formRequest->status = 'Rejected';
                    $formRequest->current_approver_id = null;
                    break;

                case 'Noted':
                    if ($formRequest->form_type === 'IOM') {
                        // After department head notes, send to target department head
                        $targetDepartmentHead = User::where('department_id', $formRequest->to_department_id)
                                                ->where('position', 'Head')
                                                ->where('accessRole', 'Approver')
                                                ->first();

                        if (!$targetDepartmentHead) {
                            DB::rollBack();
                            return redirect()->back()->with('error', 'Target department head not found. Cannot proceed with noting.');
                        }

                        $formRequest->status = 'In Progress';
                        $formRequest->current_approver_id = $targetDepartmentHead->accnt_id;
                    }
                    break;

                case 'Approved':
                    if ($formRequest->form_type === 'IOM') {
                        if ($formRequest->status === 'In Progress' &&
                            $user->department_id === $formRequest->to_department_id && 
                            $user->position === 'Head') {
                            $formRequest->status = 'Approved';
                            $formRequest->current_approver_id = null;
                            Log::info('IOM request approved by target department head');
                        } else {
                            Log::warning('Invalid approval attempt for IOM', [
                                'status' => $formRequest->status,
                                'user_dept' => $user->department_id,
                                'target_dept' => $formRequest->to_department_id,
                                'user_position' => $user->position
                            ]);
                            DB::rollBack();
                            return redirect()->back()->with('error', 'Only the target department head can approve this request at this stage.');
                        }
                    } else {
                        // For non-IOM requests
                        $formRequest->status = 'Approved';
                        $formRequest->current_approver_id = null;
                    }
                    break;
            }

            $formRequest->save();
            DB::commit();

            $actionMessage = match($action) {
                'Approved' => 'approved',
                'Rejected' => 'rejected',
                'Noted' => 'noted',
                default => 'processed'
            };

            return redirect()->route('approvals.index')
                ->with('success', "Request {$formRequest->form_id} has been {$actionMessage}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval action failed: ' . $e->getMessage(), [
                'action' => $action,
                'form_id' => $formRequest->form_id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while processing the approval. Please try again.');
        }
    }
}
