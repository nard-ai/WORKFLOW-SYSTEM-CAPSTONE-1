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
use Exception;

class ApprovalController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of requests awaiting the current user's approval.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Get filter values from the request
        $typeFilter = $request->input('type');
        $dateRangeFilter = $request->input('date_range', 'all'); // Default to all
        $priorityFilter = $request->input('priority');

        // Check if this is a VPAA user - if so, use our special implementation
        // to ensure Head leave requests are visible
        if (
            $user->position === 'VPAA' ||
            (Department::where('department_id', $user->department_id)
                ->where(function ($q) {
                    $q->where('dept_code', 'VPAA')
                        ->orWhere('dept_name', 'like', '%Vice President for Academic Affairs%');
                })->exists() && in_array($user->accessRole, ['Approver', 'Viewer']))
        ) {

            // Get all requests using our special VPAA-specific query
            $specialVpaaRequests = \App\Http\Controllers\FixVPAAApprovals::getRequestsForVPAA();

            // Apply filters to the collection for VPAA
            if ($typeFilter) {
                $specialVpaaRequests = $specialVpaaRequests->where('form_type', $typeFilter);
            }

            // Date range filtering for collection
            if ($dateRangeFilter !== 'all') {
                $specialVpaaRequests = $specialVpaaRequests->filter(function ($item) use ($dateRangeFilter) {
                    if (!$item->date_submitted)
                        return false;

                    $submitDate = Carbon::parse($item->date_submitted);
                    $today = Carbon::today();

                    switch ($dateRangeFilter) {
                        case 'today':
                            return $submitDate->isToday();
                        case 'week':
                            return $submitDate->isCurrentWeek();
                        case 'month':
                            return $submitDate->isCurrentMonth();
                        default:
                            return true;
                    }
                });
            }

            // Priority filtering - need to join with IOM details
            if ($priorityFilter) {
                $specialVpaaRequests = $specialVpaaRequests->filter(function ($item) use ($priorityFilter) {
                    // Only IOM forms have priority
                    if ($item->form_type !== 'IOM')
                        return false;

                    // Load the IOM details if not already loaded
                    if (!$item->relationLoaded('iomDetails')) {
                        $item->load('iomDetails');
                    }

                    return $item->iomDetails &&
                        strtolower($item->iomDetails->priority) === strtolower($priorityFilter);
                });
            }

            // Skip the standard query building since we're using our special implementation
            $requests = $specialVpaaRequests;

            // Calculate stats - correct methods for a Collection, not a Query Builder
            $today = Carbon::today()->format('Y-m-d');
            $twoDaysAgo = Carbon::now()->subDays(2)->format('Y-m-d');

            $stats = [
                'pending' => $requests->whereIn('status', ['Pending', 'In Progress', 'Pending Target Department Approval'])->count(),
                'today' => $requests->filter(function ($item) use ($today) {
                    return $item->date_submitted && Carbon::parse($item->date_submitted)->format('Y-m-d') == $today;
                })->count(),
                'overdue' => $requests->filter(function ($item) use ($twoDaysAgo) {
                    return $item->date_submitted && Carbon::parse($item->date_submitted)->format('Y-m-d') < $twoDaysAgo;
                })->count(),
                'avgTime' => $this->calculateAverageProcessingTime()
            ];

            // Calculate approval rate (copied from standard implementation)
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

            // Return the view with the same variables as the standard implementation
            return view('approvals.index', [
                'formRequests' => $requests,
                'stats' => $stats,
                'approvalRate' => $approvalRate
            ]);
        }

        // Base query for requests (for non-VPAA users)
        $query = FormRequest::query()
            ->with(['requester', 'requester.department', 'approvals']);

        // Add appropriate joins for filtering
        if ($priorityFilter && $typeFilter == 'IOM') {
            $query->join('iom_details', 'form_requests.form_id', '=', 'iom_details.form_id');
        }

        // Get VPAA department ID once to avoid multiple queries
        $vpaaDepartment = Department::where('dept_code', 'VPAA')
            ->orWhere('dept_name', 'like', '%Vice President for Academic Affairs%')
            ->first();

        $isVPAADepartment = $vpaaDepartment && $user->department_id === $vpaaDepartment->department_id;
        $isVPAAPosition = $user->position === 'VPAA';
        // Log user information for debugging
        Log::debug('ApprovalController index: User Info', [
            'user_id' => $user->accnt_id,
            'department_id' => $user->department_id,
            'position' => $user->position,
            'isVPAADepartment' => $isVPAADepartment
        ]);

        // Determine requests awaiting action or departmental view
        $query->where(function ($mainQuery) use ($user, $isVPAADepartment) {            // For VPAA department users (any position)
            if ($isVPAADepartment && in_array($user->accessRole, ['Approver', 'Viewer'])) {
                Log::debug('ApprovalController: User is in VPAA department', [
                    'department_id' => $user->department_id
                ]);

                $mainQuery->where(function ($vpaaQuery) use ($user) {
                    // Show all requests targeted to VPAA department
                    $vpaaQuery->where(function ($targetQuery) use ($user) {
                        $targetQuery->where('to_department_id', $user->department_id)
                            ->whereIn('status', ['In Progress', 'Pending Target Department Approval', 'Pending']);
                    });
                    // Show all leave requests from Head positions in any department
                    $vpaaQuery->orWhere(function ($leaveQuery) use ($user) {
                        $leaveQuery->where('form_type', 'Leave')
                            ->where('status', 'Pending')
                            ->whereHas('requester', function ($query) {
                                $query->where('position', 'Head');
                            });

                        Log::debug('ApprovalController: Adding query for Head leave requests', [
                            'vpaa_dept_id' => $user->department_id
                        ]);
                    });

                    // Direct query for leave requests from Heads sent to VPAA dept
                    $vpaaQuery->orWhere(function ($leaveQuery) use ($user) {
                        $leaveQuery->where([
                            ['form_type', '=', 'Leave'],
                            ['status', '=', 'Pending'],
                            ['to_department_id', '=', $user->department_id],
                            ['current_approver_id', '=', $user->accnt_id]
                        ]);

                        Log::debug('ApprovalController: Adding direct query for Head leave requests to current VPAA', [
                            'current_approver_id' => $user->accnt_id
                        ]);
                    });

                    // Also show requests from VPAA department
                    $vpaaQuery->orWhere(function ($sourceQuery) use ($user) {
                        $sourceQuery->where('from_department_id', $user->department_id)
                            ->where('status', 'Pending');
                    });
                });
            } else {                // For Head position
                if ($user->position === 'Head') {
                    $mainQuery->where(function ($headQuery) use ($user) {
                        // Show pending requests from their department that haven't been noted yet
                        // BUT exclude their own leave requests (which should only be visible to VPAA)
                        $headQuery->where(function ($pendingQ) use ($user) {
                            $pendingQ->where('from_department_id', $user->department_id)
                                ->where('status', 'Pending')
                                ->where(function ($excludeHeadLeave) use ($user) {
                                    $excludeHeadLeave->where('form_type', '!=', 'Leave')
                                        ->orWhere('requested_by', '!=', $user->accnt_id);
                                })
                                ->whereDoesntHave('approvals', function ($approvalQ) use ($user) {
                                    $approvalQ->where('approver_id', $user->accnt_id)
                                        ->where('action', 'Noted');
                                });
                        });
                        // Show in-progress requests for their department only after being noted
                        $headQuery->orWhere(function ($inProgressQ) use ($user) {
                            $inProgressQ->where('to_department_id', $user->department_id)
                                ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                                ->whereHas('approvals', function ($approvalQ) {
                                    // Ensure there's at least one 'Noted' approval
                                    $approvalQ->where('action', 'Noted');
                                });
                        });
                    });
                } else {                    // For Staff position (both Approver and Viewer)
                    if ($user->position === 'Staff') {
                        $mainQuery->where(function ($staffQuery) use ($user) {                            // Show all requests from their department, but exclude leave requests from Head
                            $staffQuery->where(function ($fromDept) use ($user) {
                                $fromDept->where('from_department_id', $user->department_id)
                                    ->where('status', 'Pending');

                                // Get all head users in the department
                                $headUsers = \App\Models\User::where('position', 'Head')
                                    ->where('department_id', $user->department_id)
                                    ->pluck('accnt_id')
                                    ->toArray();

                                // Exclude leave requests from department heads
                                if (!empty($headUsers)) {
                                    $fromDept->where(function ($query) use ($headUsers) {
                                        $query->where('form_type', '!=', 'Leave')
                                            ->orWhereNotIn('requested_by', $headUsers);
                                    });
                                }
                            });
                            // Show requests assigned to their department only after being noted by source department head
                            $staffQuery->orWhere(function ($toDept) use ($user) {
                                $toDept->where('to_department_id', $user->department_id)
                                    ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                                    ->whereHas('approvals', function ($approvalQ) {
                                        // Ensure there's at least one 'Noted' approval
                                        $approvalQ->where('action', 'Noted');
                                    });
                            });
                        });
                    }
                }
            }
        })->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled']);        // Apply type filter
        if ($typeFilter) {
            $query->where('form_type', $typeFilter);
        }

        // Apply date range filter
        if ($dateRangeFilter && $dateRangeFilter !== 'all') {
            switch ($dateRangeFilter) {
                case 'today':
                    $query->whereDate('date_submitted', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('date_submitted', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('date_submitted', Carbon::now()->month)
                        ->whereYear('date_submitted', Carbon::now()->year);
                    break;
            }
        }

        // Apply priority filter (only for IOM requests)
        if ($priorityFilter && $typeFilter === 'IOM') {
            $query->whereHas('iomDetails', function ($q) use ($priorityFilter) {
                $q->where('priority', $priorityFilter);
            });
        }

        // Calculate statistics
        $pendingCount = (clone $query)->whereIn('status', ['Pending', 'In Progress', 'Pending Target Department Approval'])->count();

        $todayCount = (clone $query)
            ->whereDate('date_submitted', Carbon::today())
            ->count();

        $overdueCount = (clone $query)
            ->where('date_submitted', '<', Carbon::now()->subDays(2))
            ->count();

        $stats = [
            'pending' => $pendingCount,
            'today' => $todayCount,
            'overdue' => $overdueCount,
            'avgTime' => $this->calculateAverageProcessingTime()
        ];

        // Get the final paginated results - ensure we have the relations loaded for filtering
        $query->with(['iomDetails', 'leaveDetails']);
        $formRequests = $query->latest('date_submitted')->paginate(10);

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
            'formRequests' => $formRequests,
            'stats' => $stats,
            'approvalRate' => $approvalRate
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

                // Check if user has permission to act on this request based on status, position, and department
                $canApprove = false;

                // VPAA can approve all requests since they are higher than department heads
                if ($user->position === 'VPAA' && $user->accessRole === 'Approver') {
                    $canApprove = true;  // VPAA can approve any request in any state
                } else {
                    // For other roles, check normal authorization rules
                    if ($user->position === 'Head') {
                        // Department Heads can approve requests in their department
                        $canApprove = ($formRequest->status === 'Pending' && $formRequest->from_department_id === $user->department_id) ||
                            (in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval']) &&
                                $formRequest->to_department_id === $user->department_id);
                    } else {
                        // Regular approvers need explicit permissions
                        $canApprove = $user->canApproveStatus($formRequest->status) && (
                            ($formRequest->status === 'Pending' && $formRequest->from_department_id === $user->department_id) ||
                            (in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval']) &&
                                $formRequest->to_department_id === $user->department_id)
                        );
                    }
                }

                if (!$canApprove) {
                    $errors[] = "No permission to {$request->action} request {$formId}. Check request status and department permissions.";
                    continue;
                }

                // Additional business rule: Staff should not be able to action leave requests from their department head
                if ($user->position === 'Staff' && $formRequest->form_type === 'Leave') {
                    // Check if the requester is a Head in the same department
                    $requesterIsHead = User::where('accnt_id', $formRequest->requested_by)
                        ->where('department_id', $user->department_id)
                        ->where('position', 'Head')
                        ->exists();

                    if ($requesterIsHead) {
                        $errors[] = "Request {$formId}: Staff members cannot process leave requests from their department head.";
                        continue;
                    }
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
                        'signature_data' => $request->signature,
                        'signature_style_id' => $request->signature_style_id, // Added this line
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
                            // For IOM requests
                            $targetDepartment = Department::find($formRequest->to_department_id);

                            if (!$targetDepartment) {
                                Log::error('Target department not found in database for IOM routing.', [
                                    'form_id' => $formRequest->form_id,
                                    'to_department_id' => $formRequest->to_department_id
                                ]);
                                throw new Exception('Target department ID is invalid. Request cannot be submitted.');
                            }

                            Log::info('IOM Routing: Checking Target Department Details', [
                                'form_id' => $formRequest->form_id,
                                'form_request_to_department_id' => $formRequest->to_department_id,
                                'fetched_target_department_id' => $targetDepartment->department_id,
                                'fetched_target_department_code' => $targetDepartment->dept_code,
                                'fetched_target_department_name' => $targetDepartment->dept_name,
                            ]);

                            // Check if the identified target department is VPAA
                            $isVPAADepartment = (strtoupper($targetDepartment->dept_code) === 'VPAA') ||
                                (stripos($targetDepartment->dept_name, 'Vice President for Academic Affairs') !== false);

                            Log::info('IOM Routing: VPAA Department Check Result', [
                                'form_id' => $formRequest->form_id,
                                'is_VPAA_department' => $isVPAADepartment,
                                'condition1_dept_code_match_raw_value' => $targetDepartment->dept_code,
                                'condition1_dept_code_match_result' => (strtoupper($targetDepartment->dept_code) === 'VPAA'),
                                'condition2_dept_name_match_raw_value' => $targetDepartment->dept_name,
                                'condition2_dept_name_match_result' => (stripos($targetDepartment->dept_name, 'Vice President for Academic Affairs') !== false),
                            ]);

                            if ($isVPAADepartment) {
                                // For VPAA department, get the VPAA position holder
                                $vpaaUserWithVpaaPosition = User::where('department_id', $targetDepartment->department_id)
                                    ->where('position', 'VPAA')
                                    ->where('accessRole', 'Approver')
                                    ->first();

                                if ($vpaaUserWithVpaaPosition) {
                                    $formRequest->current_approver_id = $vpaaUserWithVpaaPosition->accnt_id;
                                } else {
                                    Log::warning('User with VPAA position not found in VPAA department for IOM routing', [
                                        'form_id' => $formRequest->form_id,
                                        'vpaa_dept_id' => $targetDepartment->department_id
                                    ]);
                                    throw new Exception('Approver with VPAA position not found in the VPAA department. Request cannot be submitted.');
                                }
                            } else {
                                // For other departments, route to department head as usual
                                $targetDepartmentHead = User::where('department_id', $targetDepartment->department_id)
                                    ->where('position', 'Head')
                                    ->where('accessRole', 'Approver')
                                    ->first();

                                if (!$targetDepartmentHead) {
                                    Log::warning('Department Head not found for IOM routing (target was not identified as VPAA).', [
                                        'form_id' => $formRequest->form_id,
                                        'department_id' => $targetDepartment->department_id,
                                        'target_dept_code' => $targetDepartment->dept_code,
                                        'target_dept_name' => $targetDepartment->dept_name
                                    ]);
                                    throw new Exception('Target department head not found. Request cannot be submitted.');
                                }
                                $formRequest->current_approver_id = $targetDepartmentHead->accnt_id;
                            }
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
                        'signature_data' => $request->signature,
                        'signature_style_id' => $request->signature_style_id
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
            Log::error('Batch approval error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        $this->authorize('view-approvals');

        $user = auth()->user();

        // Load relationships
        $formRequest->load([
            'requester',
            'requester.department',
            'fromDepartment',
            'toDepartment',
            'iomDetails',
            'leaveDetails',
            'approvals.approver.employeeInfo',
            'approvals.approver.signatureStyle',
            'approvals.signatureStyleApplied'
        ]);

        // Get VPAA department info
        $userDepartment = $user->department;
        $isVPAADepartment = $userDepartment && (
            $userDepartment->dept_code === 'VPAA' ||
            str_contains(strtoupper($userDepartment->dept_name), 'VICE PRESIDENT FOR ACADEMIC AFFAIRS')
        );

        // Debug log
        \Log::info('Show method info:', [
            'user_id' => $user->accnt_id,
            'position' => $user->position,
            'department' => $userDepartment ? [
                'id' => $userDepartment->department_id,
                'name' => $userDepartment->dept_name,
                'code' => $userDepartment->dept_code
            ] : null,
            'accessRole' => $user->accessRole,
            'isVPAADepartment' => $isVPAADepartment
        ]);

        // Initialize permission flags
        $canTakeAction = false;
        $canApprovePending = false;
        $canApproveInProgress = false;

        // Check base permissions from ApproverPermission table for Staff
        if ($user->position === 'Staff' && $user->accessRole === 'Approver') {
            $permissions = $user->approverPermissions;
            if ($permissions) {
                $canApprovePending = $permissions->can_approve_pending;
                $canApproveInProgress = $permissions->can_approve_in_progress;
                \Log::info('Staff permissions loaded:', [
                    'user_id' => $user->accnt_id,
                    'can_approve_pending' => $canApprovePending,
                    'can_approve_in_progress' => $canApproveInProgress
                ]);
            }
        }

        // VPAA position can always take action
        if ($user->position === 'VPAA' && $user->accessRole === 'Approver') {
            $canTakeAction = true;
            $canApprovePending = true;
            $canApproveInProgress = true;
            \Log::info('VPAA full permissions granted');
        }
        // Head position can always take action on their department's requests
        elseif ($user->position === 'Head' && $user->accessRole === 'Approver') {
            $canTakeAction =
                ($formRequest->status === 'Pending' && $formRequest->from_department_id === $user->department_id) ||
                (in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval']) &&
                    $formRequest->to_department_id === $user->department_id);

            if ($canTakeAction) {
                $canApprovePending = true;
                $canApproveInProgress = true;
            }
            \Log::info('Head permissions check:', [
                'canTakeAction' => $canTakeAction
            ]);
        }
        // Staff position needs specific permissions and queue check
        elseif ($user->position === 'Staff' && $user->accessRole === 'Approver') {
            // For VPAA Staff, check their assigned permissions
            if ($isVPAADepartment) {
                // For Pending status
                if ($formRequest->status === 'Pending' && $canApprovePending) {
                    $canTakeAction = true;
                }
                // For In Progress/PTA status
                elseif (
                    in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval']) &&
                    $canApproveInProgress
                ) {
                    $canTakeAction = true;
                }
            } else {
                // For non-VPAA Staff
                // For Pending status
                if ($formRequest->status === 'Pending') {
                    $canTakeAction = $canApprovePending &&
                        $formRequest->from_department_id === $user->department_id;
                }
                // For In Progress/PTA status
                elseif (in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval'])) {
                    $canTakeAction = $canApproveInProgress &&
                        $formRequest->to_department_id === $user->department_id;
                }
            }

            \Log::info('Staff permissions and queue check:', [
                'canTakeAction' => $canTakeAction,
                'status' => $formRequest->status,
                'canApprovePending' => $canApprovePending,
                'canApproveInProgress' => $canApproveInProgress,
                'from_dept' => $formRequest->from_department_id,
                'to_dept' => $formRequest->to_department_id,
                'user_dept' => $user->department_id
            ]);

            // Staff cannot process leave requests from their department head
            if ($canTakeAction && $formRequest->form_type === 'Leave' && !$isVPAADepartment) {
                $requesterIsHead = User::where('accnt_id', $formRequest->requested_by)
                    ->where('department_id', $user->department_id)
                    ->where('position', 'Head')
                    ->exists();

                if ($requesterIsHead) {
                    $canTakeAction = false;
                    \Log::info('Disabled action for non-VPAA staff on head\'s leave request');
                }
            }
        }

        \Log::info('Final permission values:', [
            'canTakeAction' => $canTakeAction,
            'canApprovePending' => $canApprovePending,
            'canApproveInProgress' => $canApproveInProgress,
            'form_id' => $formRequest->form_id,
            'form_type' => $formRequest->form_type,
            'status' => $formRequest->status
        ]);

        return view('approvals.show', compact('formRequest', 'canTakeAction', 'canApprovePending', 'canApproveInProgress'));
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

        try {
            // Check if signature styles exist, otherwise run the seeder
            $stylesCount = \App\Models\SignatureStyle::count();
            if ($stylesCount == 0) {
                \Log::warning('No signature styles found, running seeder');
                $seeder = new \Database\Seeders\SignatureStyleSeeder();
                $seeder->run();
            }

            // Log the rejection attempt with details for debugging
            \Log::info('Rejection attempt', [
                'user_id' => auth()->id(),
                'form_id' => $formRequest->form_id,
                'has_comments' => $request->has('comments'),
                'comments_length' => $request->has('comments') ? strlen($request->comments) : 0,
                'has_signature' => $request->has('signature'),
                'has_signature_style' => $request->has('signatureStyle'),
                'signature_styles_count' => $stylesCount
            ]);

            // Validate that comments are provided for rejection
            $validatedData = $request->validate([
                'comments' => 'required|string|min:5|max:1000',
                'signature' => 'required|string',
                'signatureStyle' => 'required|exists:signature_styles,id'
            ], [
                'comments.required' => 'A reason for rejection is required.',
                'comments.min' => 'The rejection reason must be at least 5 characters.',
                'signature.required' => 'Your signature is required.',
                'signatureStyle.required' => 'Please select a signature style.',
                'signatureStyle.exists' => 'The selected signature style is not valid.'
            ]);

            return $this->processApprovalAction($request, $formRequest, 'Rejected');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Rejection validation failed', [
                'errors' => $e->errors(),
                'form_id' => $formRequest->form_id,
                'user_id' => auth()->id()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Error in reject method:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_id' => $formRequest->form_id
            ]);
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    private function processApprovalAction(Request $request, FormRequest $formRequest, string $action): RedirectResponse
    {
        $user = Auth::user();

        try {
            // Get VPAA department info
            $userDepartment = $user->department;
            $isVPAADepartment = $userDepartment && (
                $userDepartment->dept_code === 'VPAA' ||
                str_contains(strtoupper($userDepartment->dept_name), 'VICE PRESIDENT FOR ACADEMIC AFFAIRS')
            );

            // Debug log the action attempt and request details
            \Log::info('Processing approval action:', [
                'user_id' => $user->accnt_id,
                'position' => $user->position,
                'department' => $userDepartment ? [
                    'id' => $userDepartment->department_id,
                    'name' => $userDepartment->dept_name,
                    'code' => $userDepartment->dept_code
                ] : null,
                'isVPAADepartment' => $isVPAADepartment,
                'action' => $action,
                'form_id' => $formRequest->form_id,
                'form_type' => $formRequest->form_type,
                'form_status' => $formRequest->status
            ]);

            // Verify user has permission to act
            if ($user->accessRole !== 'Approver') {
                \Log::warning('Non-approver attempted action', [
                    'user_id' => $user->accnt_id,
                    'access_role' => $user->accessRole
                ]);
                return back()->with('error', 'Only users with Approver role can take actions on requests.');
            }

            // Check if user has permission for the current request status
            if (!$user->canApproveStatus($formRequest->status)) {
                \Log::warning('User lacks status-level permission', [
                    'user_id' => $user->accnt_id,
                    'status' => $formRequest->status
                ]);
                return back()->with('error', 'You do not have permission to approve requests at this stage.');
            }

            // Verify this specific user is the correct one to act
            $isAuthorizedToAct = false;

            // Case 1: Request is directly assigned to the user
            if ($formRequest->current_approver_id === $user->accnt_id) {
                $isAuthorizedToAct = true;
                \Log::info('Authorized to act: Direct assignee');
            }

            // Case 2: User is VPAA
            if (!$isAuthorizedToAct && $user->position === 'VPAA' && $user->accessRole === 'Approver') {
                $isAuthorizedToAct = true;
                \Log::info('Authorized to act: VPAA position');
            }

            // Case 3: User is VPAA Staff Approver handling a request in VPAA queue
            if (!$isAuthorizedToAct && $user->position === 'Staff' && $isVPAADepartment && $user->accessRole === 'Approver') {
                $permissions = $user->approverPermissions;

                // For leave requests from department heads
                if ($formRequest->form_type === 'Leave') {
                    $requester = User::with('department')
                        ->where('accnt_id', $formRequest->requested_by)
                        ->first();

                    \Log::info('Leave request requester details:', [
                        'requester_id' => $requester ? $requester->accnt_id : null,
                        'requester_position' => $requester ? $requester->position : null,
                        'requester_dept' => $requester && $requester->department ? [
                            'id' => $requester->department->department_id,
                            'name' => $requester->department->dept_name
                        ] : null
                    ]);

                    // Check permissions based on request status
                    if ($formRequest->status === 'Pending' && $permissions && $permissions->can_approve_pending) {
                        $isAuthorizedToAct = true;
                        \Log::info('Authorized to act: VPAA Staff with Pending permission on Leave request');
                    } elseif ($formRequest->status === 'In Progress' && $permissions && $permissions->can_approve_in_progress) {
                        $isAuthorizedToAct = true;
                        \Log::info('Authorized to act: VPAA Staff with In Progress permission on Leave request');
                    }
                } else {
                    // For non-leave requests, check departmental queue and permissions
                    if ($formRequest->status === 'Pending' && $permissions && $permissions->can_approve_pending) {
                        $isAuthorizedToAct =
                            $formRequest->from_department_id === $user->department_id ||
                            $formRequest->to_department_id === $user->department_id;
                        \Log::info('VPAA Staff Pending permission check for non-leave request:', [
                            'isAuthorizedToAct' => $isAuthorizedToAct,
                            'status' => $formRequest->status,
                            'from_dept' => $formRequest->from_department_id,
                            'to_dept' => $formRequest->to_department_id,
                            'user_dept' => $user->department_id
                        ]);
                    } elseif ($formRequest->status === 'In Progress' && $permissions && $permissions->can_approve_in_progress) {
                        $isAuthorizedToAct =
                            $formRequest->to_department_id === $user->department_id;
                    }
                }
            }

            // Case 4: Regular Staff/Head with unassigned requests in their queue
            if (!$isAuthorizedToAct && !$isVPAADepartment && ($user->position === 'Head' || ($user->position === 'Staff' && $user->accessRole === 'Approver'))) {
                $permissions = $user->approverPermissions;

                if ($formRequest->status === 'Pending' && ($user->position === 'Head' || ($permissions && $permissions->can_approve_pending))) {
                    $isAuthorizedToAct = $formRequest->from_department_id === $user->department_id;
                } elseif (
                    in_array($formRequest->status, ['In Progress', 'Pending Target Department Approval']) &&
                    ($user->position === 'Head' || ($permissions && $permissions->can_approve_in_progress))
                ) {
                    $isAuthorizedToAct = $formRequest->to_department_id === $user->department_id;
                }

                \Log::info('Regular Staff/Head queue check:', [
                    'isAuthorizedToAct' => $isAuthorizedToAct,
                    'position' => $user->position,
                    'status' => $formRequest->status,
                    'from_dept' => $formRequest->from_department_id,
                    'to_dept' => $formRequest->to_department_id,
                    'user_dept' => $user->department_id
                ]);

                // Additional check: Staff (non-VPAA) cannot process leave requests from their head
                if ($isAuthorizedToAct && $user->position === 'Staff' && $formRequest->form_type === 'Leave') {
                    $requesterIsHead = User::where('accnt_id', $formRequest->requested_by)
                        ->where('department_id', $user->department_id)
                        ->where('position', 'Head')
                        ->exists();

                    if ($requesterIsHead) {
                        $isAuthorizedToAct = false;
                        \Log::info('Disabled authorization for non-VPAA staff trying to process head\'s leave');
                    }
                }
            }

            if (!$isAuthorizedToAct) {
                \Log::warning('Unauthorized action attempt', [
                    'user_id' => $user->accnt_id,
                    'form_id' => $formRequest->form_id,
                    'action' => $action,
                    'user_position' => $user->position,
                    'user_department' => $user->department_id,
                    'isVPAADepartment' => $isVPAADepartment,
                    'status' => $formRequest->status
                ]);
                return back()->with('error', 'This request is not currently assigned to you, not in your department\'s actionable queue, or you lack specific override permissions for this action.');
            }

            // Continue with the approval process
            DB::beginTransaction();

            // Create the approval record
            FormApproval::create([
                'form_id' => $formRequest->form_id,
                'approver_id' => $user->accnt_id,
                'action' => $action,
                'action_date' => now(),
                'comments' => $request->comments,
                'signature_name' => $request->name ?? $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName,
                'signature_data' => $request->signature,
                'signature_style_id' => $request->signatureStyle
            ]);

            // Update the request status based on the action
            if ($action === 'Noted') {
                $formRequest->status = 'In Progress';

                // Find the next approver based on the request type and target department
                if ($formRequest->form_type === 'Leave') {
                    // For leave requests, route to HR
                    $hrDepartment = Department::where(function ($query) {
                        $query->where('dept_code', 'HR')
                            ->orWhere('dept_code', 'HRD')
                            ->orWhere('dept_code', 'HRMD')
                            ->orWhere('dept_name', 'LIKE', '%Human Resource%');
                    })->first();

                    if ($hrDepartment) {
                        $hrApprover = User::where('department_id', $hrDepartment->department_id)
                            ->where('position', 'Head')
                            ->where('accessRole', 'Approver')
                            ->first();

                        if ($hrApprover) {
                            $formRequest->current_approver_id = $hrApprover->accnt_id;
                            $formRequest->to_department_id = $hrDepartment->department_id;
                            \Log::info('Routing leave request to HR', [
                                'hr_dept' => $hrDepartment->department_id,
                                'hr_approver' => $hrApprover->accnt_id
                            ]);
                        }
                    }
                } else {
                    // For IOM or other requests, check the target department
                    if ($formRequest->to_department_id) {
                        $targetDepartment = Department::find($formRequest->to_department_id);

                        // Check if target department is VPAA
                        $isVPAADepartment = $targetDepartment && (
                            strtoupper($targetDepartment->dept_code) === 'VPAA' ||
                            stripos($targetDepartment->dept_name, 'Vice President for Academic Affairs') !== false
                        );

                        \Log::info('Routing IOM after noting: Target department check', [
                            'form_id' => $formRequest->form_id,
                            'target_dept_id' => $formRequest->to_department_id,
                            'target_dept_name' => $targetDepartment ? $targetDepartment->dept_name : 'Unknown',
                            'target_dept_code' => $targetDepartment ? $targetDepartment->dept_code : 'Unknown',
                            'is_vpaa_department' => $isVPAADepartment
                        ]);

                        if ($isVPAADepartment) {
                            // For VPAA department, find user with VPAA position
                            $vpaaUser = User::where('department_id', $targetDepartment->department_id)
                                ->where('position', 'VPAA')
                                ->where('accessRole', 'Approver')
                                ->with('employeeInfo')
                                ->first();

                            if ($vpaaUser) {
                                $formRequest->current_approver_id = $vpaaUser->accnt_id;
                                \Log::info('Routing request to VPAA', [
                                    'dept_id' => $targetDepartment->department_id,
                                    'vpaa_id' => $vpaaUser->accnt_id,
                                    'vpaa_emp_id' => $vpaaUser->employeeInfo ? $vpaaUser->employeeInfo->EmpID : 'VPAA-2025-0050'
                                ]);
                            } else {
                                \Log::warning('VPAA user not found, using fallback approach');
                                // Fallback: Find any approver in VPAA department if no VPAA position exists
                                $vpaaApprover = User::where('department_id', $targetDepartment->department_id)
                                    ->where('accessRole', 'Approver')
                                    ->with('employeeInfo')
                                    ->first();

                                if ($vpaaApprover) {
                                    $formRequest->current_approver_id = $vpaaApprover->accnt_id;
                                    \Log::info('Routing request to VPAA department approver (fallback)', [
                                        'dept_id' => $targetDepartment->department_id,
                                        'approver_id' => $vpaaApprover->accnt_id,
                                        'approver_emp_id' => $vpaaApprover->employeeInfo ? $vpaaApprover->employeeInfo->EmpID : 'VPAA-2025-0050'
                                    ]);
                                }
                            }
                        } else {
                            // For non-VPAA departments, find the department head
                            $nextApprover = User::where('department_id', $formRequest->to_department_id)
                                ->where('position', 'Head')
                                ->where('accessRole', 'Approver')
                                ->first();

                            if ($nextApprover) {
                                $formRequest->current_approver_id = $nextApprover->accnt_id;
                                \Log::info('Routing request to target department head', [
                                    'dept_id' => $formRequest->to_department_id,
                                    'approver_id' => $nextApprover->accnt_id
                                ]);
                            }
                        }
                    }
                }
            } elseif ($action === 'Approved') {
                $formRequest->status = 'Approved';
                $formRequest->current_approver_id = null;
            } else { // Rejected
                $formRequest->status = 'Rejected';
                $formRequest->current_approver_id = null;
            }

            $formRequest->save();
            DB::commit();

            return redirect()->route('approvals.index')
                ->with('success', "Request has been {$action} successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing approval action:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while processing your action. Please try again.');
        }
    }

    private function calculateAverageProcessingTime(): string
    {
        $user = Auth::user();
        $completedRequests = FormRequest::where(function ($query) use ($user) {
            $query->where('current_approver_id', $user->accnt_id)
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
