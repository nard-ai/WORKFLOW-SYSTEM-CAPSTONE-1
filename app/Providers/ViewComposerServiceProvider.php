<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\FormRequest;
use Illuminate\Support\Facades\Auth;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('layouts.navigation', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                
                $pendingCount = FormRequest::where(function($query) use ($user) {
                    $query->where(function($q1) use ($user) {
                        // From department pending noting
                        $q1->where('from_department_id', $user->department_id)
                           ->where('status', 'Pending');
                    })->orWhere(function($q2) use ($user) {
                        // To department pending approval
                        $q2->where('to_department_id', $user->department_id)
                           ->whereIn('status', ['In Progress', 'Pending Target Department Approval']);
                    });
                })
                ->whereNotIn('status', ['Approved', 'Rejected', 'Cancelled'])
                ->count();
            } else {
                $pendingCount = 0;
            }

            $view->with('pendingApprovalCount', $pendingCount);
        });
    }
} 