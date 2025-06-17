<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{    /**
     * Get the count of pending approvals for the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCount()
    {
        $currentUser = Auth::user();
        $userAccntId = $currentUser->accnt_id;
        $userDepartmentId = $currentUser->department_id;
        $userPosition = $currentUser->position;
        $userAccessRole = $currentUser->accessRole;

        // Debug the user information only in debug mode
        if (config('app.debug')) {
            Log::debug('Getting notification count for user', [
                'user_id' => $userAccntId,
                'position' => $userPosition,
                'accessRole' => $userAccessRole,
                'department_id' => $userDepartmentId
            ]);
        }

        // IMPORTANT: For VPAA users, use the same logic as ApprovalController awaiting count
        // This ensures notification badge matches the awaiting action count
        $vpaaDepartment = \App\Models\Department::where('dept_code', 'VPAA')
            ->orWhere('dept_name', 'like', '%Vice President for Academic Affairs%')
            ->first();
        $isVPAADepartment = $vpaaDepartment && $currentUser->department_id === $vpaaDepartment->department_id;

        if (
            $currentUser->position === 'VPAA' ||
            ($isVPAADepartment && in_array($currentUser->accessRole, ['Approver', 'Viewer']))
        ) {
            // Use the same VPAA-specific logic as ApprovalController
            $vpaaRequests = \App\Http\Controllers\FixVPAAApprovals::getRequestsForVPAA();
            $pendingCount = $vpaaRequests->count();

            // Debug logging for VPAA users
            if (config('app.debug')) {
                Log::debug('VPAA BADGE COUNT - Using FixVPAAApprovals logic', [
                    'user_id' => $userAccntId,
                    'position' => $userPosition,
                    'access_role' => $userAccessRole,
                    'department_id' => $userDepartmentId,
                    'count' => $pendingCount,
                    'request_ids' => $vpaaRequests->pluck('form_id')->toArray()
                ]);
            }
            return response()->json([
                'count' => $pendingCount
            ]);
        }

        // For non-VPAA users, use the existing logic
        // Start building the query for pending approvals
        $pendingCountQuery = FormRequest::query()
            // First exclude the user's own requests 
            ->where('requested_by', '!=', $userAccntId)
            ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled']) // Put this at top level
            ->where(function ($mainQuery) use ($userAccntId, $userDepartmentId, $userPosition, $userAccessRole) {
                // 1. Requests directly assigned to the user via current_approver_id
                $mainQuery->where('current_approver_id', $userAccntId);                // For Staff, Head, and VPAA users that have approval permissions
                if ($userAccessRole === 'Approver' || $userAccessRole === 'Viewer') {
                    // Note: VPAA users are handled separately above, so this branch won't execute for them
    
                    // For Department Head: Include requests from their department without a specific approver
                    if ($userPosition === 'Head') {
                        // Requests FROM the user's department, status 'Pending', and current_approver_id is NULL.
                        $mainQuery->orWhere(function ($subQuery) use ($userDepartmentId) {
                            $subQuery->where('from_department_id', $userDepartmentId)
                                ->where('status', 'Pending')
                                ->whereNull('current_approver_id');
                        });

                        // Requests TO the user's department, status 'In Progress', and current_approver_id is NULL.
                        $mainQuery->orWhere(function ($subQuery) use ($userDepartmentId) {
                            $subQuery->where('to_department_id', $userDepartmentId)
                                ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                                ->whereNull('current_approver_id');
                        });
                    }// For Staff (both Approver and Viewer): Do not use this query branch at all
                    // Staff notification count comes exclusively from the direct query below
                    if ($userPosition === 'Staff') {
                        // BUGFIX: Don't add any conditions to this branch
                        // This ensures we don't double-count or include items that should be excluded
    
                        // Important: Add a condition that will never match to disable this part of the query
                        $mainQuery->orWhereRaw('1=0'); // Effectively turns off this branch for Staff
                    }
                }
            });        // For Staff users, always use the simplified query that matches ApprovalController
        if ($userPosition === 'Staff' && ($userAccessRole === 'Approver' || $userAccessRole === 'Viewer')) {
            // CRITICAL FIX: Use a clean, direct query for Staff badge count
            // Build a fresh query that matches exactly what's shown in the table in ApprovalController
            $staffApprovalQuery = \App\Models\FormRequest::query()
                // Removed: ->where('requested_by', '!=', $userAccntId) to match table behavior
                ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled'])
                ->where(function ($mainQ) use ($userDepartmentId) {
                    // Logic for requests FROM the Staff's department
                    $mainQ->where(function ($fromDeptQuery) use ($userDepartmentId) {
                        $fromDeptQuery->where('from_department_id', $userDepartmentId)
                            ->where('status', 'Pending');

                        // Get all head users in the department
                        $headUsers = \App\Models\User::where('position', 'Head')
                            ->where('department_id', $userDepartmentId)
                            ->pluck('accnt_id')
                            ->toArray();

                        // Exclude leave requests from department heads
                        if (!empty($headUsers)) {
                            $fromDeptQuery->where(function ($query) use ($headUsers) {
                                $query->where('form_type', '!=', 'Leave')
                                    ->orWhereNotIn('requested_by', $headUsers);
                            });
                        }
                    });

                    // Logic for requests TO the Staff's department (must be 'Noted')
                    $mainQ->orWhere(function ($toDeptQuery) use ($userDepartmentId) {
                        $toDeptQuery->where('to_department_id', $userDepartmentId)
                            ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                            ->whereHas('approvals', function ($approvalQ) {
                                $approvalQ->where('action', 'Noted');
                            });
                    });
                });

            $pendingCount = $staffApprovalQuery->count();

            // Debug logging for Staff users
            if (config('app.debug')) {
                Log::debug('STAFF BADGE COUNT - Using Simplified Query', [
                    'user_id' => $userAccntId,
                    'count' => $pendingCount,
                    'department_id' => $userDepartmentId,
                    'query_sql' => $staffApprovalQuery->toSql(),
                    'query_bindings' => $staffApprovalQuery->getBindings()
                ]);
            }
        } else {
            // Execute the count query for non-staff, non-VPAA users (mainly Heads)
            $pendingCount = $pendingCountQuery->count();
        }
        // Debug the final count only in debug mode
        if (config('app.debug')) {
            Log::debug('Notification count for user', [
                'user_id' => $userAccntId,
                'position' => $userPosition,
                'accessRole' => $userAccessRole,
                'count' => $pendingCount
            ]);
        }

        return response()->json([
            'count' => $pendingCount
        ]);
    }
}
