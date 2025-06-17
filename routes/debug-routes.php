<?php

use Illuminate\Support\Facades\Route;

// Debug routes - remove in production
Route::get('/debug/check-head-leaves', function () {
    // Find all leave requests from Head positions
    $headLeaves = \App\Models\FormRequest::where('form_type', 'Leave')
        ->whereHas('requester', function ($query) {
            $query->where('position', 'Head');
        })
        ->with(['requester', 'requester.department', 'toDepartment'])
        ->get();

    echo "<h2>Head Leave Requests</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>From</th><th>Department</th><th>To Department</th><th>Status</th><th>Current Approver</th></tr>";

    foreach ($headLeaves as $leave) {
        $currentApprover = \App\Models\User::find($leave->current_approver_id);
        $approverInfo = $currentApprover ? $currentApprover->accnt_id . ' - ' . ($currentApprover->employeeInfo ? $currentApprover->employeeInfo->FirstName . ' ' . $currentApprover->employeeInfo->LastName : 'Unknown') : 'None';

        echo "<tr>";
        echo "<td>{$leave->form_id}</td>";
        echo "<td>{$leave->requester->accnt_id} - " . ($leave->requester->employeeInfo ? $leave->requester->employeeInfo->FirstName . ' ' . $leave->requester->employeeInfo->LastName : 'Unknown') . "</td>";
        echo "<td>" . ($leave->requester->department ? $leave->requester->department->dept_name : 'Unknown') . "</td>";
        echo "<td>" . ($leave->toDepartment ? $leave->toDepartment->dept_name : 'Not Set') . "</td>";
        echo "<td>{$leave->status}</td>";
        echo "<td>{$approverInfo}</td>";
        echo "</tr>";
    }

    echo "</table>";

    // Find VPAA department
    $vpaaDept = \App\Models\Department::where('dept_code', 'VPAA')
        ->orWhere('dept_name', 'like', '%Vice President for Academic Affairs%')
        ->first();

    if ($vpaaDept) {
        echo "<h2>VPAA Department Users</h2>";
        $vpaaUsers = \App\Models\User::where('department_id', $vpaaDept->department_id)->get();

        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Position</th><th>Role</th></tr>";

        foreach ($vpaaUsers as $user) {
            echo "<tr>";
            echo "<td>{$user->accnt_id}</td>";
            echo "<td>" . ($user->employeeInfo ? $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName : 'Unknown') . "</td>";
            echo "<td>{$user->position}</td>";
            echo "<td>{$user->accessRole}</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>VPAA Department not found</p>";
    }
});
