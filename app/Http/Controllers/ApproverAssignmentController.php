<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ApproverAssignmentController extends Controller
{
    /**
     * Display a listing of department staff for approver assignment.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Only allow access if user is a Department Head and an Approver
        if ($user->position !== 'Head' || $user->accessRole !== 'Approver') {
            abort(403, 'Only department heads can manage approvers.');
        }

        // Get all staff from the same department
        $departmentStaff = User::where('department_id', $user->department_id)
                            ->where('accnt_id', '!=', $user->accnt_id)
                            ->where('position', '!=', 'Head') // Don't show other heads
                            ->orderBy('username')
                            ->get();

        return view('approver-assignments.index', compact('departmentStaff'));
    }

    /**
     * Update the approver status of a staff member.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $authenticatedUser = Auth::user();
        
        // Validate that the authenticated user is a Department Head and an Approver
        if ($authenticatedUser->position !== 'Head' || $authenticatedUser->accessRole !== 'Approver') {
            return redirect()->back()->with('error', 'Only department heads can manage approvers.');
        }

        // Validate that the target user is in the same department and not a head
        if ($user->department_id !== $authenticatedUser->department_id || $user->position === 'Head') {
            return redirect()->back()->with('error', 'You can only manage approver status of staff in your department.');
        }

        // Validate the request
        $validated = $request->validate([
            'is_approver' => 'required|boolean'
        ]);

        try {
            DB::beginTransaction();

            $user->accessRole = $validated['is_approver'] ? 'Approver' : 'Requester';
            $user->save();

            DB::commit();

            return redirect()->route('approver-assignments.index')
                ->with('success', "{$user->username}'s approver status has been updated successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'An error occurred while updating the approver status.');
        }
    }
} 