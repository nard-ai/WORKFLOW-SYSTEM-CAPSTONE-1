<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Added
use App\Models\FormRequest; // Import the FormRequest model
use Illuminate\Support\Facades\Auth; // Import Auth for potential direct checks if needed
use Carbon\Carbon; // Import Carbon for date manipulation

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        $tabs = [
            'all_requests' => 'All Requests',
            'pending' => 'Pending',
            'approved' => 'Approved', // Changed from 'completed' to 'approved' for consistency
            'rejected' => 'Rejected',
            // Add other admin-specific tabs if needed
        ];

        $activeTab = $request->query('tab', array_key_first($tabs));
        if (!array_key_exists($activeTab, $tabs)) {
            $activeTab = array_key_first($tabs);
        }

        $allRequestsQuery = FormRequest::with([
            'requester.employeeInfo',
            'requester.department',
            'iomDetails',     // Ensure iomDetails is eager loaded
            'leaveDetails',   // Ensure leaveDetails is eager loaded
            'approvals' // Eager load approvals for processing time and status
        ]);

        // --- Statistics Calculations ---
        $now = Carbon::now();

        // Monthly Count (for the current month)
        $monthlyCount = (clone $allRequestsQuery)->whereYear('date_submitted', $now->year)
            ->whereMonth('date_submitted', $now->month)
            ->count();
        // Yearly Count (for the current year)
        $yearlyCount = (clone $allRequestsQuery)->whereYear('date_submitted', $now->year)
            ->count();

        // All requests for the current year for further stats
        $requestsThisYear = (clone $allRequestsQuery)->whereYear('date_submitted', $now->year)->get();

        // Average Processing Time (for completed/rejected requests this year)
        $completedOrRejectedRequests = $requestsThisYear->filter(function ($req) {
            return in_array(strtolower($req->status), ['approved', 'rejected']) && $req->date_submitted && $req->approvals->isNotEmpty();
        });

        $totalProcessingDays = 0;
        $processedCount = 0;
        foreach ($completedOrRejectedRequests as $req) {
            $submittedDate = Carbon::parse($req->date_submitted);
            // Find the latest approval/rejection date
            $finalActionDate = null;
            foreach ($req->approvals as $approval) {
                if (in_array(strtolower($approval->action), ['approved', 'rejected'])) {
                    $actionDate = Carbon::parse($approval->action_date);
                    if ($finalActionDate === null || $actionDate->gt($finalActionDate)) {
                        $finalActionDate = $actionDate;
                    }
                }
            }
            if ($finalActionDate) {
                $totalProcessingDays += $submittedDate->diffInDays($finalActionDate);
                $processedCount++;
            }
        }
        $avgProcessingTime = $processedCount > 0 ? round($totalProcessingDays / $processedCount, 1) . ' days' : 'N/A';


        // Approval Rate (for completed requests this year)
        $totalDecidedRequests = $requestsThisYear->whereIn('status', ['Approved', 'Rejected'])->count();
        $approvedRequestsCount = $requestsThisYear->where('status', 'Approved')->count();
        $approvalRate = $totalDecidedRequests > 0 ? round(($approvedRequestsCount / $totalDecidedRequests) * 100, 1) : 0;

        // --- Tab Counts ---
        $counts = [];
        $allDbRequests = (clone $allRequestsQuery)->orderBy('date_submitted', 'desc')->get(); // Get all for counts and initial table

        $counts['all_requests'] = $allDbRequests->count();
        $counts['pending'] = $allDbRequests->whereIn('status', ['Pending', 'In Progress', 'Pending Department Head Approval', 'Pending Target Department Approval'])->count(); // Sum of all pending-like statuses
        $counts['approved'] = $allDbRequests->where('status', 'Approved')->count();
        $counts['rejected'] = $allDbRequests->where('status', 'Rejected')->count();


        // Filter requests based on activeTab for display
        // This part is tricky if we want client-side DataTables to handle all data.
        // For now, let's pass all requests and let DataTables handle filtering if possible,
        // or adjust this if server-side filtering per tab is strictly needed with DataTables.
        // For simplicity with initial DataTables setup, we pass all requests.
        // If server-side processing for DataTables is implemented later, this will change.

        $requestsForTable = $allDbRequests; // Pass all requests to the view

        // If you were to filter by tab server-side (and not rely on DataTables for this):
        // switch ($activeTab) {
        //     case 'pending':
        //         $requestsForTable = $allDbRequests->whereIn('status', ['Pending', 'In Progress', 'Pending Department Head Approval', 'Pending Target Department Approval'])->values();
        //         break;
        //     case 'approved':
        //         $requestsForTable = $allDbRequests->where('status', 'Approved')->values();
        //         break;
        //     case 'rejected':
        //         $requestsForTable = $allDbRequests->where('status', 'Rejected')->values();
        //         break;
        //     case 'all_requests':
        //     default:
        //         $requestsForTable = $allDbRequests;
        //         break;
        // }


        return view('admin.dashboard', [
            'requests' => $requestsForTable, // Use this for the table
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'monthlyCount' => $monthlyCount,
            'yearlyCount' => $yearlyCount,
            'avgProcessingTime' => $avgProcessingTime,
            'approvalRate' => $approvalRate,
            'counts' => $counts, // Pass tab counts
        ]);
    }

    /**
     * Display the specified resource for tracking by admin.
     *
     * @param  string  $formId
     * @return \Illuminate\View\View
     */
    public function showRequestTrack($formId)
    {
        $formRequest = FormRequest::with([
            'requester.employeeInfo', // For requester's name
            'requester.department',   // For requester's department
            'fromDepartment',         // Department submitting the request (if different from requester's, or for context)
            'toDepartment',           // Target department for IOMs
            'iomDetails',
            'leaveDetails',
            'approvals.approver.employeeInfo', // For approver names in timeline
            'approvals.approver.signatureStyle',      // Corrected: For displaying signatures if available, via approver
            'currentApprover.employeeInfo'   // For displaying current approver's name
        ])->findOrFail($formId);

        // Admin should be able to view any request, so no specific authorization check here beyond the 'admin' middleware on the route.

        return view('admin.requests.track', compact('formRequest'));
    }
}
