<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
// use App\Models\RequestModel; // Old model
use App\Models\FormRequest; // New model
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard.
     */
    public function index(Request $request): View
    {
        try {
            $user = Auth::user();
            $departmentName = $user->department ? $user->department->dept_name : 'N/A';
            
            // Get the active tab from the query parameter, default to 'all'
            $activeTab = $request->query('tab', 'all');

            // Base query for user's requests with eager loading
            $baseQuery = FormRequest::with(['iomDetails', 'leaveDetails', 'fromDepartment', 'toDepartment', 'approvals.approver'])
                                ->where('requested_by', $user->accnt_id);

            // Get counts for tabs first
            $counts = [
                'all' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->whereIn('status', [
                    'Pending', 
                    'In Progress', 
                    'Pending Department Head Approval', 
                    'Pending Target Department Approval'
                ])->count(),
                'completed' => (clone $baseQuery)->where('status', 'Approved')->count(),
                'rejected' => (clone $baseQuery)->where('status', 'Rejected')->count(),
            ];

            // Apply filters based on active tab
            $requests = match($activeTab) {
                'pending' => (clone $baseQuery)->whereIn('status', [
                    'Pending', 
                    'In Progress', 
                    'Pending Department Head Approval', 
                    'Pending Target Department Approval'
                ]),
                'completed' => (clone $baseQuery)->where('status', 'Approved'),
                'rejected' => (clone $baseQuery)->where('status', 'Rejected'),
                default => clone $baseQuery,
            };

            // Get the paginated results
            $requests = $requests->latest('date_submitted')->paginate(10)->withQueryString();

            // Calculate monthly and yearly counts
            $now = Carbon::now();
            $monthlyCount = (clone $baseQuery)
                ->whereYear('date_submitted', $now->year)
                ->whereMonth('date_submitted', $now->month)
                ->count();

            $yearlyCount = (clone $baseQuery)
                ->whereYear('date_submitted', $now->year)
                ->count();

            // Calculate average processing time for completed requests
            $completedRequests = (clone $baseQuery)
                ->where('status', 'Approved')
                ->with(['approvals' => function($query) {
                    $query->whereIn('action', ['Approved', 'Submitted']);
                }])
                ->get();

            $avgProcessingTime = 'N/A';
            if ($completedRequests->isNotEmpty()) {
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

                if ($completedCount > 0) {
                    $avgProcessingTime = round($totalProcessingTime / $completedCount) . ' hrs';
                }
            }

            // Calculate approval rate
            $totalFinalized = (clone $baseQuery)
                ->whereIn('status', ['Approved', 'Rejected'])
                ->count();

            $totalApproved = (clone $baseQuery)
                ->where('status', 'Approved')
                ->count();

            $approvalRate = $totalFinalized > 0 
                ? round(($totalApproved / $totalFinalized) * 100)
                : 0;

            // Debug information
            \Log::info('Dashboard Query Info', [
                'user_id' => $user->accnt_id,
                'active_tab' => $activeTab,
                'total_requests' => $counts['all'],
                'filtered_requests' => $requests->total(),
                'current_page_count' => $requests->count(),
            ]);

            return view('dashboard', [
                'departmentName' => $departmentName,
                'position' => $user->position,
                'requests' => $requests,
                'activeTab' => $activeTab,
                'counts' => $counts,
                'monthlyCount' => $monthlyCount,
                'yearlyCount' => $yearlyCount,
                'avgProcessingTime' => $avgProcessingTime,
                'approvalRate' => $approvalRate,
            ]);

        } catch (\Exception $e) {
            // Log the error and return with a friendly message
            \Log::error('Dashboard Error: ' . $e->getMessage());
            return view('dashboard', [
                'departmentName' => $departmentName ?? 'N/A',
                'position' => $user->position ?? 'N/A',
                'requests' => collect([]),
                'activeTab' => 'all',
                'counts' => ['all' => 0, 'pending' => 0, 'completed' => 0, 'rejected' => 0],
                'monthlyCount' => 0,
                'yearlyCount' => 0,
                'avgProcessingTime' => 'N/A',
                'approvalRate' => 0,
                'error' => 'There was an error loading the dashboard. Please try again later.'
            ]);
        }
    }
}
