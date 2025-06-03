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
            if (Auth::check() && Auth::user()->accessRole === 'Approver') {
                $pendingCount = FormRequest::where(function($query) {
                    $query->where('current_approver_id', Auth::id())
                        ->orWhere(function($q) {
                            $q->whereNull('current_approver_id')
                              ->where('status', 'Pending')
                              ->whereHas('requester', function($q2) {
                                  $q2->where('department_id', Auth::user()->department_id);
                              });
                        });
                })
                ->whereIn('status', ['Pending', 'In Progress', 'Pending Department Head Approval'])
                ->count();
            } else {
                $pendingCount = 0;
            }

            $view->with('pendingApprovalCount', $pendingCount);
        });
    }
} 