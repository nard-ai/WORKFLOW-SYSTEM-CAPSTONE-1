<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FormRequest;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Enhanced VPAA approval controller - WORKFLOW-CORRECTED VERSION
 * 
 * This controller implements the correct workflow sequence for when VPAA should see requests.
 * ALL VPAA department users (both Staff and VPAA positions) can see workflow-appropriate requests.
 * Action permissions (Note/Approve/Reject) are controlled separately by accessRole and ApproverPermissions.
 * 
 * WORKFLOW RULES:
 * 1. HEAD LEAVE REQUESTS: Go directly to VPAA (skip department head noting)
 *    - Status: "Pending" 
 *    - Target: VPAA department
 *    - Requester: Position = "Head"
 *    - VISIBLE TO: All VPAA staff (for tracking/delegation)
 * 
 * 2. STAFF REQUESTS: Must be noted by department head first, then go to target department
 *    - Staff → Department Head (Noted) → Target Department (if applicable)
 *    - VPAA sees these AFTER department head has noted them
 *    - Status: "Pending Target Department Approval" with Head "Noted" approval
 *    - VISIBLE TO: All VPAA staff (for tracking/delegation)
 * 
 * 3. HEAD IOM REQUESTS: Can go directly to target department (including VPAA)
 *    - No department head noting required for Head's own requests
 *    - Status: "Pending Target Department Approval" 
 *    - VISIBLE TO: All VPAA staff (for tracking/delegation)
 * 
 * 4. INTERNAL VPAA REQUESTS: Requests within VPAA department
 *    - From VPAA dept to VPAA dept
 *    - Status: "Pending"
 *    - VISIBLE TO: All VPAA staff
 * 
 * 5. STAFF VISIBILITY: Staff in VPAA department can see all workflow-appropriate requests
 *    - They can only ACT on them if they have accessRole='Approver' and proper ApproverPermissions
 *    - Read-only access for Viewer role or Staff without specific permissions
 *    - Delegation scenarios handled through current_approver_id assignment
 * 
 * This fixes the issue where VPAA was seeing requests prematurely before the proper
 * workflow sequence was completed, while ensuring Staff in VPAA department have
 * appropriate visibility for tracking and delegation purposes.
 */
class FixVPAAApprovals
{    /**
     * Get requests that are actually ready for VPAA approval or should be visible to VPAA department
     * Shows requests when it's VPAA's turn in the approval workflow
     * ALL VPAA department users (Staff and VPAA positions) can see these requests
     * Action permissions are controlled separately by accessRole and ApproverPermissions
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRequestsForVPAA()
    {
        $user = Auth::user();

        // First check if user is in VPAA department
        $vpaaDepartment = Department::where('dept_code', 'VPAA')
            ->orWhere('dept_name', 'like', '%Vice President for Academic Affairs%')
            ->first();

        if (!$vpaaDepartment || $user->department_id !== $vpaaDepartment->department_id) {
            return collect(); // Not VPAA department, return empty collection
        }

        // Build query for ALL VPAA department users (both Staff and VPAA positions)
        // Visibility is controlled here, action permissions are controlled in the view/controller
        $query = FormRequest::with(['requester', 'requester.department', 'approvals.approver'])
            ->where('status', '!=', 'Draft')
            ->where(function ($q) use ($user, $vpaaDepartment) {

                // CASE 1: Head Leave Requests - Direct to VPAA (Skip department head noting)
                // All VPAA staff should see these for tracking/visibility
                $q->where(function ($headLeaveQuery) use ($vpaaDepartment) {
                    $headLeaveQuery->where('form_type', 'Leave')
                        ->where('status', 'Pending')
                        ->where('to_department_id', $vpaaDepartment->department_id)
                        ->whereHas('requester', function ($query) {
                            $query->where('position', 'Head');
                        });
                });

                // CASE 2: IOM Requests targeted to VPAA after proper workflow
                // All VPAA staff should see these for tracking/visibility
                $q->orWhere(function ($iomQuery) use ($vpaaDepartment) {
                    $iomQuery->where('form_type', 'IOM')
                        ->where('to_department_id', $vpaaDepartment->department_id)
                        ->where('status', 'Pending Target Department Approval')
                        ->where(function ($workflowCheck) {
                            // Either from Head (direct to VPAA) OR has department head noting
                            $workflowCheck->whereHas('requester', function ($requesterQuery) {
                                $requesterQuery->where('position', 'Head');
                            })
                                ->orWhereHas('approvals', function ($approvalQuery) {
                                $approvalQuery->where('action', 'Noted')
                                    ->whereHas('approver', function ($approverQuery) {
                                        $approverQuery->where('position', 'Head');
                                    });
                            });
                        });
                });

                // CASE 3: Staff Leave Requests that have been noted by Head and targeted to VPAA
                // All VPAA staff should see these for tracking/visibility
                $q->orWhere(function ($staffLeaveQuery) use ($vpaaDepartment) {
                    $staffLeaveQuery->where('form_type', 'Leave')
                        ->where('to_department_id', $vpaaDepartment->department_id)
                        ->where('status', 'Pending Target Department Approval')
                        ->whereHas('requester', function ($query) {
                            $query->where('position', 'Staff');
                        })
                        ->whereHas('approvals', function ($approvalQuery) {
                            $approvalQuery->where('action', 'Noted')
                                ->whereHas('approver', function ($approverQuery) {
                                    $approverQuery->where('position', 'Head');
                                });
                        });
                });

                // CASE 4: Internal VPAA department requests (within VPAA)
                // All VPAA staff should see these for tracking/visibility
                $q->orWhere(function ($internalQuery) use ($vpaaDepartment) {
                    $internalQuery->where('from_department_id', $vpaaDepartment->department_id)
                        ->where('to_department_id', $vpaaDepartment->department_id)
                        ->where('status', 'Pending');
                });

                // CASE 5: Requests where any VPAA department user is explicitly set as current approver
                // This handles delegation scenarios where Staff might be assigned
                $q->orWhere(function ($directQuery) use ($user) {
                    $directQuery->where('current_approver_id', $user->accnt_id)
                        ->whereIn('status', ['Pending', 'In Progress', 'Pending Target Department Approval']);
                });

                // CASE 6: Additional visibility for all workflow-appropriate requests to VPAA
                // This ensures Staff can see all requests that are at the VPAA stage
                $q->orWhere(function ($vpaaStageQuery) use ($vpaaDepartment) {
                    $vpaaStageQuery->where('to_department_id', $vpaaDepartment->department_id)
                        ->whereIn('status', ['Pending', 'Pending Target Department Approval', 'In Progress'])
                        ->where(function ($workflowValidation) {
                            // Only show if proper workflow prerequisites are met
                            $workflowValidation
                                // Head requests (no prior approval needed)
                                ->whereHas('requester', function ($requesterQuery) {
                                $requesterQuery->where('position', 'Head');
                            })
                                // OR Staff requests that have been noted by head
                                ->orWhere(function ($staffWithApproval) {
                                $staffWithApproval->whereHas('requester', function ($requesterQuery) {
                                    $requesterQuery->where('position', 'Staff');
                                })
                                    ->whereHas('approvals', function ($approvalQuery) {
                                        $approvalQuery->where('action', 'Noted')
                                            ->whereHas('approver', function ($approverQuery) {
                                                $approverQuery->where('position', 'Head');
                                            });
                                    });
                            });
                        });
                });
            })
            // Safety check: Only show active requests
            ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled', 'Withdrawn']);

        $results = $query->get();

        // Additional filtering to ensure workflow correctness - but less restrictive for visibility
        $filteredResults = $results->filter(function ($request) use ($vpaaDepartment, $user) {
            // For Leave requests from Heads - should always be visible to ALL VPAA staff
            if (
                $request->form_type === 'Leave' &&
                $request->requester &&
                $request->requester->position === 'Head' &&
                $request->to_department_id === $vpaaDepartment->department_id
            ) {
                return true;
            }

            // For Staff requests - they should be visible if they've reached the VPAA stage
            if ($request->requester && $request->requester->position === 'Staff') {
                // If it's targeted to VPAA and has head noting, show it
                if ($request->to_department_id === $vpaaDepartment->department_id) {
                    $hasHeadNoting = $request->approvals->contains(function ($approval) {
                        return $approval->action === 'Noted' &&
                            $approval->approver &&
                            $approval->approver->position === 'Head';
                    });

                    // Show if workflow is complete OR if user is specifically assigned
                    return $hasHeadNoting || $request->current_approver_id === $user->accnt_id;
                }

                // If status is still "Pending" and no head noting, only show if specifically assigned
                if ($request->status === 'Pending') {
                    return $request->current_approver_id === $user->accnt_id;
                }
            }

            // For IOM from Staff to VPAA - show if head has noted or if specifically assigned
            if (
                $request->form_type === 'IOM' &&
                $request->requester &&
                $request->requester->position === 'Staff' &&
                $request->to_department_id === $vpaaDepartment->department_id
            ) {
                $hasHeadNoting = $request->approvals->contains(function ($approval) {
                    return $approval->action === 'Noted' &&
                        $approval->approver &&
                        $approval->approver->position === 'Head';
                });

                return $hasHeadNoting || $request->current_approver_id === $user->accnt_id;
            }

            // For internal VPAA requests - always visible to VPAA staff
            if (
                $request->from_department_id === $vpaaDepartment->department_id &&
                $request->to_department_id === $vpaaDepartment->department_id
            ) {
                return true;
            }

            // For any other requests targeted to VPAA - show them for visibility
            if ($request->to_department_id === $vpaaDepartment->department_id) {
                return true;
            }

            return false;
        });

        // Debug log for VPAA requests (can be removed in production)
        \Illuminate\Support\Facades\Log::debug('VPAA Requests Retrieved', [
            'user_id' => $user->accnt_id,
            'user_position' => $user->position,
            'user_access_role' => $user->accessRole,
            'department_id' => $user->department_id,
            'total_query_count' => $results->count(),
            'filtered_count' => $filteredResults->count(),
            'request_ids' => $filteredResults->pluck('form_id')->toArray(),
            'request_details' => $filteredResults->map(function ($r) {
                return [
                    'form_id' => $r->form_id,
                    'type' => $r->form_type,
                    'status' => $r->status,
                    'requester_position' => $r->requester->position ?? 'Unknown',
                    'from_dept' => $r->from_department_id,
                    'to_dept' => $r->to_department_id,
                    'current_approver' => $r->current_approver_id,
                    'has_head_approval' => $r->approvals->contains(function ($a) {
                        return $a->action === 'Noted' && $a->approver && $a->approver->position === 'Head';
                    })
                ];
            })->toArray()
        ]);

        return $filteredResults;
    }
}
