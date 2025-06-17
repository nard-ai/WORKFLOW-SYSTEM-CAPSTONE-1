<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\FormRequest;
use Illuminate\Support\Facades\Auth;

class ViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.navigation', function ($view) {
            if (Auth::check()) {
                $currentUser = Auth::user();
                $userAccntId = $currentUser->accnt_id; // Use accnt_id as in ApprovalController
                $userDepartmentId = $currentUser->department_id;
                $userPosition = $currentUser->position;
                $userAccessRole = $currentUser->accessRole;

                // IMPORTANT: For VPAA users, use the same logic as ApprovalController and NotificationController
                // This ensures all three sources (ViewComposer, ApprovalController, NotificationController) are synchronized
                $vpaaDepartment = \App\Models\Department::where('dept_code', 'VPAA')
                    ->orWhere('dept_name', 'like', '%Vice President for Academic Affairs%')
                    ->first();
                $isVPAADepartment = $vpaaDepartment && $currentUser->department_id === $vpaaDepartment->department_id;

                if (
                    $currentUser->position === 'VPAA' ||
                    ($isVPAADepartment && in_array($currentUser->accessRole, ['Approver', 'Viewer']))
                ) {
                    // Use the same VPAA-specific logic as ApprovalController and NotificationController
                    $vpaaRequests = \App\Http\Controllers\FixVPAAApprovals::getRequestsForVPAA();
                    $pendingCount = $vpaaRequests->count();

                    $view->with('pendingApprovalCount', $pendingCount);
                    return; // Early return for VPAA users
                }

                // For non-VPAA users, use the existing logic
                $pendingCountQuery = FormRequest::query()
                    // First exclude the user's own requests 
                    ->where('requested_by', '!=', $userAccntId)
                    ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled']) // Put this at top level
                    ->where(function ($mainQuery) use ($userAccntId, $userDepartmentId, $userPosition, $userAccessRole) {
                        // 1. Requests directly assigned to the user via current_approver_id
                        $mainQuery->where('current_approver_id', $userAccntId);

                        // For Staff, Head users that have approval permissions (VPAA handled above)
                        if ($userAccessRole === 'Approver' || $userAccessRole === 'Viewer') {
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
                            }
                            // For Staff (both Approver and Viewer)
                            if ($userPosition === 'Staff') {
                                $mainQuery->orWhere(function ($staffQuery) use ($userDepartmentId) {
                                    // Show all requests from their department, but exclude leave requests from Head
                                    $staffQuery->where(function ($fromDept) use ($userDepartmentId) {
                                        $fromDept->where('from_department_id', $userDepartmentId)
                                            ->where('status', 'Pending');

                                        // Get head users in the department
                                        $headUsers = \App\Models\User::where('position', 'Head')
                                            ->where('department_id', $userDepartmentId)
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

                                    // Show requests assigned to their department only after being noted
                                    $staffQuery->orWhere(function ($toDept) use ($userDepartmentId) {
                                        $toDept->where('to_department_id', $userDepartmentId)
                                            ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                                            ->whereHas('approvals', function ($approvalQ) {
                                                $approvalQ->where('action', 'Noted');
                                            });
                                    });
                                });
                            }
                        }
                    });

                $pendingCount = $pendingCountQuery->count();

                // For Staff users, ensure the count matches exactly what's shown in the Approvals page
                if ($userPosition === 'Staff' && ($userAccessRole === 'Approver' || $userAccessRole === 'Viewer')) {
                    // CRITICAL FIX: Use a clean, direct query for Staff badge count
                    // Build a fresh query that matches exactly what's shown in the table in ApprovalController
                    $staffApprovalQuery = FormRequest::query()
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
                }

                $view->with('pendingApprovalCount', $pendingCount);
            } else {
                $pendingCount = 0;
            }
        });
    }
}