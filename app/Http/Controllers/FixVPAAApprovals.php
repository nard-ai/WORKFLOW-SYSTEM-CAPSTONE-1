<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FormRequest;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Quick fix to ensure Head leave requests are visible to VPAA department
 */
class FixVPAAApprovals
{
    /**
     * Get all requests awaiting VPAA approval, including Head leave requests
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

        // Start building the query for VPAA department users
        $query = FormRequest::with(['requester', 'requester.department', 'approvals'])
            ->where('status', '!=', 'Draft')
            ->where(function ($q) use ($user, $vpaaDepartment) {
                // Regular requests targeted to VPAA department
                $q->where('to_department_id', $user->department_id)
                    ->whereIn('status', ['In Progress', 'Pending Target Department Approval', 'Pending']);

                // Include ALL leave requests from Head positions
                $q->orWhere(function ($leaveQuery) {
                    $leaveQuery->where('form_type', 'Leave')
                        ->where('status', 'Pending')
                        ->whereHas('requester', function ($query) {
                            $query->where('position', 'Head');
                        });
                });

                // Include requests from VPAA department
                $q->orWhere(function ($sourceQuery) use ($user) {
                    $sourceQuery->where('from_department_id', $user->department_id)
                        ->where('status', 'Pending');
                });
            });

        return $query->get();
    }
}
