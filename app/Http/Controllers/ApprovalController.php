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
use Illuminate\Support\Facades\Log; // Ensure Log facade is imported
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\SignatureStyle;
use App\Models\Department;

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

        // Determine requests awaiting the current user's action
        $query->where(function ($mainQuery) use ($user) {
            // 1. Requests directly assigned to the user via current_approver_id
            $mainQuery->where('current_approver_id', $user->accnt_id);

            // 2. OR, if the user is a Head or VPAA (acting as Head for their dept staff),
            //    handle standard department-based workflows for requests NOT YET specifically assigned.
            if ($user->position === 'Head' || $user->position === 'VPAA') {
                // 2a. Requests FROM the user's department, status 'Pending', and current_approver_id is NULL.
                // This is for a Head/VPAA to 'Note' their own staff's initial submissions.
                $mainQuery->orWhere(function ($subQuery) use ($user) {
                    $subQuery->where('from_department_id', $user->department_id)
                        ->where('status', 'Pending')
                        ->whereNull('current_approver_id');
                });

                // 2b. Requests TO the user's department, status 'In Progress' or 'Pending Target Department Approval',
                //     and current_approver_id is NULL.
                // This is for a Head/VPAA to 'Approve' requests routed to their department (e.g. IOMs).
                $mainQuery->orWhere(function ($subQuery) use ($user) {
                    $subQuery->where('to_department_id', $user->department_id)
                        ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                        ->whereNull('current_approver_id');
                });
            }
        })
            ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled']); // Applied to all results

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

        // Create a base query for all pending requests in the department for STATS
        // This should mirror the logic of the main $query for consistency
        $pendingRequestsQuery = FormRequest::query()
            ->where(function ($mainQuery) use ($user) {
                // 1. Requests directly assigned to the user via current_approver_id
                $mainQuery->where('current_approver_id', $user->accnt_id);

                // 2. OR, if the user is a Head or VPAA
                if ($user->position === 'Head' || $user->position === 'VPAA') {
                    // 2a. From department, Pending, no specific approver
                    $mainQuery->orWhere(function ($subQuery) use ($user) {
                        $subQuery->where('from_department_id', $user->department_id)
                            ->where('status', 'Pending')
                            ->whereNull('current_approver_id');
                    });
                    // 2b. To department, In Progress/Pending Target, no specific approver
                    $mainQuery->orWhere(function ($subQuery) use ($user) {
                        $subQuery->where('to_department_id', $user->department_id)
                            ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                            ->whereNull('current_approver_id');
                    });
                }
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
        $totalFinalized = FormRequest::where(function ($q) use ($user) {
            $q->where('from_department_id', $user->department_id)
                ->orWhere('to_department_id', $user->department_id);
        })
            ->whereIn('status', ['Approved', 'Rejected'])
            ->count();

        $totalApproved = FormRequest::where(function ($q) use ($user) {
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
                'signature' => 'required|string'
            ]);

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

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some requests could not be processed.',
                    'errors' => $errors
                ], 422);
            }

            if (empty($requestsToProcess)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid requests to process.'
                ], 422);
            }

            DB::beginTransaction();

            foreach ($requestsToProcess as $formRequest) {
                if ($action === 'Approve') {
                    // Create approval record
                    FormApproval::create([
                        'form_id' => $formRequest->form_id,
                        'approver_id' => $user->accnt_id,
                        'action' => $formRequest->status === 'Pending' ? 'Noted' : 'Approved',
                        'action_date' => now(),
                        'comments' => $request->comment,
                        'signature_name' => $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName,
                        'signature_data' => $request->signature
                    ]);

                    // Update request status
                    if ($formRequest->status === 'Pending') {
                        $formRequest->status = 'In Progress';

                        // For leave requests, route to HR
                        if ($formRequest->form_type === 'Leave') {
                            $hrDepartment = Department::where('dept_code', 'HR')
                                ->orWhere('dept_code', 'HRD')
                                ->orWhere('dept_code', 'HRMD')
                                ->orWhere('dept_name', 'like', '%Human Resource%')
                                ->first();

                            if ($hrDepartment) {
                                $hrApprover = User::where('department_id', $hrDepartment->department_id)
                                    ->where('position', 'Head')
                                    ->where('accessRole', 'Approver')
                                    ->first();

                                if ($hrApprover) {
                                    $formRequest->current_approver_id = $hrApprover->accnt_id;
                                    $formRequest->to_department_id = $hrDepartment->department_id;
                                }
                            }
                        } else {
                            // For IOM requests, route to target department head
                            $targetDepartmentHead = User::where('department_id', $formRequest->to_department_id)
                                ->where('position', 'Head')
                                ->where('accessRole', 'Approver')
                                ->first();
                            $formRequest->current_approver_id = $targetDepartmentHead ? $targetDepartmentHead->accnt_id : null;
                        }
                    } else {
                        // For In Progress requests, mark as Approved
                        $formRequest->status = 'Approved';
                        $formRequest->current_approver_id = null;
                    }
                } else {
                    // Handle rejection
                    FormApproval::create([
                        'form_id' => $formRequest->form_id,
                        'approver_id' => $user->accnt_id,
                        'action' => 'Rejected',
                        'action_date' => now(),
                        'comments' => $request->comment,
                        'signature_name' => $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName,
                        'signature_data' => $request->signature
                    ]);

                    $formRequest->status = 'Rejected';
                    $formRequest->current_approver_id = null;
                }

                $formRequest->save();
                $successCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$successCount} request(s) processed successfully."
            ]);

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
            $user->department_id === $formRequest->to_department_id ||
            $formRequest->current_approver_id === $user->accnt_id; // User can also view if they are the current approver

        if (!$canView && $user->accessRole !== 'Admin') { // Admins can view all
            abort(403, 'You are not authorized to view this request.');
        }

        $formRequest->load(['requester.department', 'fromDepartment', 'toDepartment', 'iomDetails', 'leaveDetails', 'approvals.approver']);

        // Determine if the current user can take action based on their permissions
        $canTakeAction = $user->canApproveStatus($formRequest->status) && (
                // Case 1: Request is directly assigned to the current user
            ($formRequest->current_approver_id === $user->accnt_id) ||
                // Case 2: Request is not assigned to anyone specific (current_approver_id is NULL) AND
                //         it's in the user's departmental queue (if they are a Head or VPAA acting as Head for their dept)
            (is_null($formRequest->current_approver_id) && ($user->position === 'Head' || $user->position === 'VPAA') &&
                (
                    ($formRequest->status === 'Pending' && $user->department_id === $formRequest->from_department_id) ||
                    (in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval']) && $user->department_id === $formRequest->to_department_id)
                )
            )
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

        try {
            // Verify user has permission to act on this request based on their general approval capabilities for the status
            if (!$user->canApproveStatus($formRequest->status)) {
                return back()->with('error', 'You do not have permission to approve requests at this stage.');
            }

            // Verify this specific user is the correct one to act on this specific request instance
            $isCorrectDepartmentOrUser = match ($formRequest->status) {
                'Pending' => ($user->position === 'Head' && $user->department_id === $formRequest->from_department_id && $formRequest->current_approver_id === $user->accnt_id) || // Dept Head noting their staff's request
                ($formRequest->current_approver_id === $user->accnt_id), // Specific user (like VPAA or initial Dept Head) is the current approver for a Pending request
                'In Progress', 'Pending Target Department Approval' =>
                ($user->position === 'Head' && $user->department_id === $formRequest->to_department_id && $formRequest->current_approver_id === $user->accnt_id) || // Target Dept Head approving
                ($formRequest->current_approver_id === $user->accnt_id), // Specific user (like HR Head) is current approver for In Progress
                default => false
            };


            if (!$isCorrectDepartmentOrUser) {
                Log::warning('Approval attempt by incorrect department/user', [
                    'form_id' => $formRequest->form_id,
                    'form_status' => $formRequest->status,
                    'form_current_approver' => $formRequest->current_approver_id,
                    'form_from_dept' => $formRequest->from_department_id,
                    'form_to_dept' => $formRequest->to_department_id,
                    'user_id' => $user->accnt_id,
                    'user_dept' => $user->department_id,
                    'user_position' => $user->position,
                    'action_taken' => $action
                ]);
                return back()->with('error', 'This request is not currently assigned to you or your department for action.');
            }

            // Validate comments if required
            if ($action === 'Rejected' && empty($request->comments)) {
                return back()->with('error', 'Comments are required when rejecting a request.');
            }

            DB::beginTransaction();

            // Create approval record
            FormApproval::create([
                'form_id' => $formRequest->form_id,
                'approver_id' => $user->accnt_id,
                'action' => $action,
                'action_date' => now(),
                'comments' => $request->comments,
                'signature_name' => $request->name ?? ($user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName),
                'signature_data' => $request->signature ?? null,
                'signature_style_id' => $request->signatureStyle ?? null,
            ]);

            // Determine the new status based on current status and action
            $newStatus = match ($action) {
                'Noted' => 'In Progress',
                'Approved' => 'Approved',
                'Rejected' => 'Rejected',
                default => $formRequest->status
            };

            $targetApproverId = null;
            $newToDepartmentId = $formRequest->to_department_id; // Default: keep current to_department_id

            if ($newStatus === 'In Progress') { // This means action was 'Noted'
                // Identify if current acting user is VPAA
                $vpaaDepartment = Department::where('dept_code', 'VPAA')
                    ->orWhere('dept_name', 'Vice President for Academic Affairs')
                    ->first();
                $isCurrentUserVPAA = $vpaaDepartment &&
                    $user->department_id === $vpaaDepartment->department_id &&
                    $user->position === 'VPAA' &&
                    $user->accessRole === 'Approver';

                if ($formRequest->form_type === 'Leave') {
                    // Case 1: VPAA (current user) 'Noted' a Leave request.
                    // This leave request was made by another Department Head and was 'Pending' for VPAA.
                    if ($isCurrentUserVPAA && $formRequest->current_approver_id === $user->accnt_id) {
                        $hrDepartment = Department::where('dept_code', 'HR')
                            ->orWhere('dept_code', 'HRD')
                            ->orWhere('dept_code', 'HRMD')
                            ->orWhere('dept_name', 'like', '%Human Resource%')
                            ->first();

                        if (!$hrDepartment) {
                            DB::rollBack();
                            Log::error('HR Department not found for VPAA to HR routing.', ['form_id' => $formRequest->form_id]);
                            return back()->with('error', 'HR Department not found. Please contact your administrator.');
                        }

                        $hrApprover = User::where('department_id', $hrDepartment->department_id)
                            ->where('position', 'Head')
                            ->where('accessRole', 'Approver')
                            ->first();

                        if (!$hrApprover) {
                            DB::rollBack();
                            Log::error('HR Department Head not found for VPAA to HR routing.', ['form_id' => $formRequest->form_id, 'hr_department_id' => $hrDepartment->department_id]);
                            return back()->with('error', 'HR Department Head not found. Please contact your administrator.');
                        }

                        $targetApproverId = $hrApprover->accnt_id;
                        $newToDepartmentId = $hrDepartment->department_id; // Update to_department_id to HR
                        Log::info('Leave request (from Dept Head) noted by VPAA, routed to HR.', [
                            'form_id' => $formRequest->form_id,
                            'vpaa_user_id' => $user->accnt_id,
                            'hr_approver_id' => $targetApproverId
                        ]);
                    } else {
                        // Case 2: Regular Department Head (current user, not VPAA) 'Noted' their staff's Leave request.
                        // The request was 'Pending' for this Dept Head.
                        // Or a Dept Head's leave request that fell back to HR in RequestController (already 'In Progress' for HR, this path not taken for 'Noted').
                        // This path is for: Dept Head (current_approver_id) notes their staff's leave.
                        $hrDepartment = Department::where('dept_code', 'HR')
                            ->orWhere('dept_code', 'HRD')
                            ->orWhere('dept_code', 'HRMD')
                            ->orWhere('dept_name', 'like', '%Human Resource%')
                            ->first();

                        if (!$hrDepartment) {
                            DB::rollBack();
                            Log::error('HR Department not found for standard leave routing.', ['form_id' => $formRequest->form_id]);
                            return back()->with('error', 'HR Department not found. Please contact your administrator.');
                        }

                        $hrApprover = User::where('department_id', $hrDepartment->department_id)
                            ->where('position', 'Head')
                            ->where('accessRole', 'Approver')
                            ->first();

                        if (!$hrApprover) {
                            DB::rollBack();
                            Log::error('HR Department Head not found for standard leave routing.', ['form_id' => $formRequest->form_id, 'hr_department_id' => $hrDepartment->department_id]);
                            return back()->with('error', 'HR Department Head not found. Please contact your administrator.');
                        }
                        $targetApproverId = $hrApprover->accnt_id;
                        $newToDepartmentId = $hrDepartment->department_id; // Update to_department_id to HR
                        Log::info('Leave request (from Staff or fallback) noted by Dept Head, routed to HR.', [
                            'form_id' => $formRequest->form_id,
                            'noter_id' => $user->accnt_id,
                            'hr_approver_id' => $targetApproverId
                        ]);
                    }
                } elseif ($formRequest->form_type === 'IOM') {
                    // IOM 'Noted' by source Dept Head (current_approver_id)
                    // Route to Target Department Head
                    $targetDepartmentHead = User::where('department_id', $formRequest->to_department_id)
                        ->where('position', 'Head')
                        ->where('accessRole', 'Approver')
                        ->first();

                    if (!$targetDepartmentHead) {
                        DB::rollBack();
                        Log::error('Target Department Head for IOM not found', [
                            'form_id' => $formRequest->form_id,
                            'target_dept_id' => $formRequest->to_department_id
                        ]);
                        return back()->with('error', 'Target Department Head for IOM not found. Please contact your administrator.');
                    }
                    $targetApproverId = $targetDepartmentHead->accnt_id;
                    // $newToDepartmentId for IOM remains $formRequest->to_department_id (already correct from submission)
                    Log::info('IOM request noted by source Dept Head, routed to target Dept Head.', [
                        'form_id' => $formRequest->form_id,
                        'noter_id' => $user->accnt_id,
                        'target_approver_id' => $targetApproverId
                    ]);
                }
            }

            // Update request status, current approver, and to_department_id
            $formRequest->status = $newStatus;
            $formRequest->current_approver_id = $targetApproverId;
            // Update to_department_id if the status is now 'In Progress' (as it might have changed, e.g. for Leave to HR)
            // or 'Pending' (though 'Pending' is not set in this part of the flow, good for robustness).
            if ($newStatus === 'In Progress' || $newStatus === 'Pending') {
                $formRequest->to_department_id = $newToDepartmentId;
            }
            $formRequest->save();

            DB::commit();
            return redirect()->route('approvals.index')->with('success', "Request has been {$action}.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval process error: ' . $e->getMessage(), [
                'form_id' => $formRequest->form_id,
                'action' => $action,
                'user_id' => $user->accnt_id,
                'exception' => $e
            ]);
            return back()->with('error', 'An error occurred while processing the request. Please contact your administrator.');
        }
    }

    private function calculateAverageProcessingTime(): string
    {
        $user = Auth::user();

        $completedRequests = FormRequest::where(function ($query) use ($user) {
            $query->where('current_approver_id', $user->id)
                ->orWhere(function ($q) use ($user) {
                    $q->whereHas('requester', function ($q2) use ($user) {
                        $q2->where('department_id', $user->department_id);
                    });
                });
        })
            ->where('status', 'Approved')
            ->with([
                'approvals' => function ($query) {
                    $query->whereIn('action', ['Approved', 'Submitted']);
                }
            ])
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
