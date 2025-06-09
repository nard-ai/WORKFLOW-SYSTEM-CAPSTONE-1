<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormRequest; // Import the FormRequest model
use Illuminate\Support\Facades\Auth; // Import Auth for potential direct checks if needed

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Fetch all requests with their related data
        // Eager load relationships to avoid N+1 query problems
        $allRequests = FormRequest::with([
            'requester.employeeInfo', // To get name from employeeInfo
            'requester.department',   // To get requester's department name
            'fromDepartment',         // To get the "from" department name
            'toDepartment'            // To get the "to" department name
        ])
            ->orderBy('date_submitted', 'desc') // Show newest first
            ->get();

        return view('admin.dashboard', ['requests' => $allRequests]);
    }
}
