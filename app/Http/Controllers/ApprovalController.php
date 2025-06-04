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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\SignatureStyle;

class ApprovalController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of requests awaiting the current user's approval.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Base query for requests
        $query = FormRequest::query()
            ->with(['requester', 'requester.department', 'approvals']);

        // For all users, show requests based on their department
        $query->where(function($q) use ($user) {
            $q->where(function($q1) use ($user) {
                // Requests from their department that need noting
                $q1->where('from_department_id', $user->department_id)
                   ->where('status', 'Pending');
            })->orWhere(function($q2) use ($user) {
                // Requests to their department that need approval
                $q2->where('to_department_id', $user->department_id)
                   ->whereIn('status', ['In Progress', 'Pending Target Department Approval']);
            });
        });

        // For viewers, show all requests but filter out completed ones
        if ($user->accessRole === 'Viewer') {
            $query->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled']);
        }

        // Apply filters if present
        if (request('type')) {
            $query->where('form_type', request('type'));
        }

        if ($request->filled('date_range')) {
            $now = Carbon::now();
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('date_submitted', $now->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('date_submitted', [
                        $now->startOfWeek()->toDateTimeString(),
                        $now->endOfWeek()->toDateTimeString()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('date_submitted', $now->month)
                         ->whereYear('date_submitted', $now->year);
                    break;
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Create a base query for all pending requests in the department
        $pendingRequestsQuery = FormRequest::query()
            ->where(function($q) use ($user) {
                $q->where(function($q1) use ($user) {
                    // From department pending noting
                    $q1->where('from_department_id', $user->department_id)
                       ->where('status', 'Pending');
                })->orWhere(function($q2) use ($user) {
                    // To department pending approval
                    $q2->where('to_department_id', $user->department_id)
                       ->whereIn('status', ['In Progress', 'Pending Target Department Approval']);
                });
            })
            ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled']);
        
        // Calculate statistics
        $pendingCount = $pendingRequestsQuery->count();

        $todayCount = (clone $pendingRequestsQuery)
            ->whereDate('date_submitted', Carbon::today())
            ->count();

        $overdueCount = (clone $pendingRequestsQuery)
            ->where('date_submitted', '<', Carbon::now()->subDays(2))
            ->count();

        $stats = [
            'pending' => $pendingCount,
            'today' => $todayCount,
            'overdue' => $overdueCount,
            'avgTime' => $this->calculateAverageProcessingTime()
        ];

        // Get the final paginated results - for display we still respect the viewer/approver distinction
        $requestsToApprove = $query->latest('date_submitted')->paginate(10);

        // Calculate approval rate
        $totalFinalized = FormRequest::where(function($q) use ($user) {
                $q->where('from_department_id', $user->department_id)
                  ->orWhere('to_department_id', $user->department_id);
            })
            ->whereIn('status', ['Approved', 'Rejected'])
            ->count();

        $totalApproved = FormRequest::where(function($q) use ($user) {
                $q->where('from_department_id', $user->department_id)
                  ->orWhere('to_department_id', $user->department_id);
            })
            ->where('status', 'Approved')
            ->count();

        $approvalRate = $totalFinalized > 0 
            ? round(($totalApproved / $totalFinalized) * 100)
            : 0;

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
    public function batch(Request $request)
    {
        // Validate user is an approver
        $user = Auth::user();
        if ($user->accessRole !== 'Approver') {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform batch actions.'
            ], 403);
        }

        try {
            $request->validate([
                'selected_requests' => 'required|array',
                'selected_requests.*' => 'exists:form_requests,form_id',
                'action' => 'required|in:approve,reject',
                'comment' => 'required_if:action,reject|string|nullable',
                'signature_style_id' => 'required|exists:signature_styles,id',
                'signature' => 'required|string' // Base64 image data
            ]);

            // Verify the signature style exists
            $signatureStyle = SignatureStyle::find($request->signature_style_id);
            if (!$signatureStyle) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature style selected.'
                ], 403);
            }

            $action = ucfirst($request->action);
            $successCount = 0;
            $errors = [];

            // First validate all requests before processing
            $requestsToProcess = [];
            foreach ($request->selected_requests as $formId) {
                $formRequest = FormRequest::find($formId);
                
                if (!$formRequest) {
                    $errors[] = "Request {$formId} not found.";
                    continue;
                }

                // Check if user has permission to act on this request based on status and department
                $canApprove = $user->canApproveStatus($formRequest->status) && (
                    ($formRequest->status === 'Pending' && $formRequest->from_department_id === $user->department_id) ||
                    (in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval']) && 
                     $formRequest->to_department_id === $user->department_id)
                );

                if (!$canApprove) {
                    $errors[] = "No permission to {$request->action} request {$formId}. Check request status and department permissions.";
                    continue;
                }

                $requestsToProcess[] = $formRequest;
            }

            // If there are any errors, return without processing
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some requests could not be processed.',
                    'errors' => $errors
                ], 422);
            }

            // If no requests to process, return error
            if (empty($requestsToProcess)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid requests to process.'
                ], 422);
            }

            DB::beginTransaction();

            foreach ($requestsToProcess as $formRequest) {
                // Create approval record with signature
                FormApproval::create([
                    'form_id' => $formRequest->form_id,
                    'approver_id' => $user->accnt_id,
                    'action' => $action === 'Approve' ? 'Approved' : 'Rejected',
                    'action_date' => now(),
                    'comments' => $request->comment,
                    'signature_name' => $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName,
                    'signature_data' => $request->signature
                ]);

                // Update request status based on current status and action
                if ($action === 'Approve') {
                    if ($formRequest->status === 'Pending') {
                        $formRequest->status = 'In Progress';
                        // Find target department head
                        $targetDepartmentHead = User::where('department_id', $formRequest->to_department_id)
                            ->where('position', 'Head')
                            ->where('accessRole', 'Approver')
                            ->first();
                        $formRequest->current_approver_id = $targetDepartmentHead ? $targetDepartmentHead->accnt_id : null;
                    } else {
                        $formRequest->status = 'Approved';
                        $formRequest->current_approver_id = null;
                    }
                } else {
                    $formRequest->status = 'Rejected';
                    $formRequest->current_approver_id = null;
                }

                $formRequest->save();
                $successCount++;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "{$successCount} request(s) {$action}d successfully."
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch approval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the requests.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource for approval.
     */
    public function show(FormRequest $formRequest): View
    {
        $user = Auth::user();
        
        // Check if user's department is involved in the request
        $canView = $user->department_id === $formRequest->from_department_id || 
                  $user->department_id === $formRequest->to_department_id;

        if (!$canView) {
            abort(403, 'You are not authorized to view this request.');
        }

        $formRequest->load(['requester.department', 'fromDepartment', 'toDepartment', 'iomDetails', 'leaveDetails', 'approvals.approver']);

        // Determine if the current user can take action based on their permissions
        $canTakeAction = $user->canApproveStatus($formRequest->status) && (
            // For source department
            ($formRequest->status === 'Pending' && $user->department_id === $formRequest->from_department_id) ||
            // For target department
            (in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval']) && 
             $user->department_id === $formRequest->to_department_id)
        );

        return view('approvals.show', compact('formRequest', 'canTakeAction'));
    }

    public function note(Request $request, FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('approve-requests');
        // Comments are optional for noting
        return $this->processApprovalAction($request, $formRequest, 'Noted');
    }

    public function approve(Request $request, FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('approve-requests');
        // Comments are optional for approval
        return $this->processApprovalAction($request, $formRequest, 'Approved');
    }

    public function reject(Request $request, FormRequest $formRequest): RedirectResponse
    {
        $this->authorize('approve-requests');
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
        $user = Auth::user();
        
        // Verify user has permission to act on this request
        if (!$user->canApproveStatus($formRequest->status)) {
            return back()->with('error', 'You do not have permission to approve requests at this stage.');
        }

        // Verify department matches the current stage
        $isCorrectDepartment = match ($formRequest->status) {
            'Pending' => $user->department_id === $formRequest->from_department_id,
            'In Progress', 'Pending Target Department Approval' => $user->department_id === $formRequest->to_department_id,
            default => false
        };

        if (!$isCorrectDepartment) {
            return back()->with('error', 'This request needs to be processed by a different department at this stage.');
        }

        // Validate comments if required
        if ($action === 'Rejected' && empty($request->comments)) {
            return back()->with('error', 'Comments are required when rejecting a request.');
        }

        DB::beginTransaction();
        try {
            // Create approval record
            FormApproval::create([
                'form_id' => $formRequest->form_id,
                'approver_id' => $user->accnt_id,
                'action' => $action,
                'action_date' => now(),
                'comments' => $request->comments
            ]);

            // Update request status
            $formRequest->update([
                'status' => $action,
                'current_approver_id' => null
            ]);

            DB::commit();
            return redirect()->route('approvals.index')->with('success', "Request has been {$action}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval process error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while processing the request.');
        }
    }

    private function calculateAverageProcessingTime(): string
    {
        $user = Auth::user();
        
        $completedRequests = FormRequest::where(function($query) use ($user) {
                $query->where('current_approver_id', $user->id)
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

        return $completedCount > 0 
            ? round($totalProcessingTime / $completedCount) . 'h'
            : 'N/A';
    }
}
