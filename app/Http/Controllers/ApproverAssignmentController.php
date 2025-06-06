<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ApproverPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ApproverAssignmentController extends Controller
{
    /**
     * Display a listing of department staff for approver assignment.
     */
    public function index(): View
    {
        $employees = User::where('department_id', Auth::user()->department_id)
            ->where('accnt_id', '!=', Auth::id()) // Exclude current user
            ->with(['employeeInfo', 'approverPermissions'])
            ->get();

        return view('approver-assignments.index', compact('employees'));
    }

    /**
     * Update the approver status and permissions of a staff member.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Log the incoming request data
        Log::info('Approver assignment update request:', [
            'user_id' => $user->accnt_id,
            'request_data' => $request->except(['password_confirmation']) // Don't log the password
        ]);

        // Validate that the user being updated is in the same department
        if ($user->department_id !== Auth::user()->department_id) {
            return back()->with('error', 'You can only manage approvers in your own department.');
        }

        // Validate that we're not trying to change a Head's role
        if ($user->position === 'Head') {
            return back()->with('error', 'Department Head roles cannot be modified.');
        }

        try {
            // Validate the request
            $validated = $request->validate([
                'accessRole' => 'required|in:Viewer,Approver',
                'can_approve_pending' => 'nullable|in:0,1',
                'can_approve_in_progress' => 'nullable|in:0,1',
                'password_confirmation' => 'required|string',
            ]);

            // Verify the password using Hash::check
            if (!Hash::check($validated['password_confirmation'], Auth::user()->password)) {
                return back()->with('error', 'Invalid password. Please try again.')
                            ->withInput($request->except('password_confirmation'));
            }

            // Additional validation for Approver role
            if ($validated['accessRole'] === 'Approver') {
                if (!$request->has('can_approve_pending') && !$request->has('can_approve_in_progress')) {
                    return back()->with('error', 'At least one permission must be selected for an Approver.')
                                ->withInput($request->except('password_confirmation'));
                }
            }

            DB::beginTransaction();

            // Update the user's role
            $user->accessRole = $request->accessRole;
            $user->save();

            Log::info('User role updated:', [
                'user_id' => $user->accnt_id,
                'new_role' => $user->accessRole
            ]);

            // Update or create permissions if the user is an approver
            if ($request->accessRole === 'Approver') {
                $permissions = ApproverPermission::updateOrCreate(
                    ['accnt_id' => $user->accnt_id],
                    [
                        'can_approve_pending' => $request->input('can_approve_pending', '0') === '1',
                        'can_approve_in_progress' => $request->input('can_approve_in_progress', '0') === '1',
                    ]
                );

                Log::info('Approver permissions updated:', [
                    'user_id' => $user->accnt_id,
                    'permissions' => $permissions->toArray()
                ]);
            } else {
                // If user is changed to Viewer, remove their permissions
                ApproverPermission::where('accnt_id', $user->accnt_id)->delete();
                Log::info('Approver permissions removed for user:', ['user_id' => $user->accnt_id]);
            }

            DB::commit();
            return back()->with('success', "Successfully updated {$user->employeeInfo->FirstName}'s role and permissions.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput($request->except('password_confirmation'));
        } catch (\Exception $e) {
            Log::error('Error updating approver assignment:', [
                'user_id' => $user->accnt_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::rollBack();
            return back()->with('error', 'An error occurred while updating the user role and permissions: ' . $e->getMessage());
        }
    }
} 