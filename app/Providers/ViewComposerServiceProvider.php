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

                $pendingCountQuery = FormRequest::query()
                    ->where(function ($mainQuery) use ($userAccntId, $userDepartmentId, $userPosition) {
                        // 1. Requests directly assigned to the user via current_approver_id
                        $mainQuery->where('current_approver_id', $userAccntId);

                        // 2. OR, if the user is a Head or VPAA (for unassigned items in their department queue)
                        if ($userPosition === 'Head' || $userPosition === 'VPAA') {
                            // 2a. Requests FROM the user's department, status 'Pending', and current_approver_id is NULL.
                            $mainQuery->orWhere(function ($subQuery) use ($userDepartmentId) {
                                $subQuery->where('from_department_id', $userDepartmentId)
                                    ->where('status', 'Pending')
                                    ->whereNull('current_approver_id');
                            });

                            // 2b. Requests TO the user's department, status 'In Progress' or 'Pending Target Department Approval',
                            //     and current_approver_id is NULL.
                            $mainQuery->orWhere(function ($subQuery) use ($userDepartmentId) {
                                $subQuery->where('to_department_id', $userDepartmentId)
                                    ->whereIn('status', ['In Progress', 'Pending Target Department Approval'])
                                    ->whereNull('current_approver_id');
                            });
                        }
                    })
                    ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled']); // Match controller's exclusion

                $pendingCount = $pendingCountQuery->count();

            } else {
                $pendingCount = 0;
            }

            $view->with('pendingApprovalCount', $pendingCount);
        });
    }
}