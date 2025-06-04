<?php

namespace App\Http\Controllers;

use App\Models\FormRequest;
use App\Models\IomDetail;
use App\Models\LeaveDetail;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\FormApproval;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();
        $requests = FormRequest::with(['iomDetails', 'leaveDetails', 'fromDepartment', 'toDepartment'])
                                ->where('requested_by', $user->accnt_id)
                                ->latest('date_submitted')
                                ->paginate(10);
        
        return view('requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $departments = Department::orderBy('dept_name')->get();
        // Pass old input to the view if available (e.g., after a validation error on confirmation page)
        $formData = session()->get('form_data_for_confirmation_edit', []);
        return view('requests.create', compact('departments', 'formData')); 
    }

    /**
     * Validate and temporarily store form data for confirmation.
     */
    public function submitForConfirmation(Request $request): RedirectResponse
    {
        $requestType = $request->input('request_type');
        $user = Auth::user();

        $allRules = [
            'request_type' => ['required', 'string', Rule::in(['IOM', 'Leave'])],
        ];

        if ($requestType === 'IOM') {
            $allRules = array_merge($allRules, [
                'iom_to_department_id' => 'required|integer|exists:tb_department,department_id',
                'iom_re' => 'required|string|max:255',
                'iom_priority' => ['required', Rule::in(['Routine', 'Urgent', 'Rush'])],
                'iom_purpose' => ['required', Rule::in(['For Information', 'For Action', 'For Signature', 'For Comments', 'For Approval', 'Request'])],
                'iom_specific_request_type' => [
                    Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Request'),
                    'nullable', 
                    'string', 
                    'max:255', 
                    Rule::in(['Request for Facilities', 'Request for Computer Laboratory', 'Request for Venue'])
                ],
                'iom_description' => 'required|string|max:5000',
                'iom_date_needed' => 'nullable|date|after_or_equal:today',
            ]);
        } elseif ($requestType === 'Leave') {
            $allRules = array_merge($allRules, [
                'leave_type' => ['required', Rule::in(['sick', 'vacation', 'emergency'])],
                'date_of_leave' => 'required|date|after_or_equal:today',
                'leave_description' => 'required|string|max:1000',
            ]);
        }
        // If requestType is invalid or empty, the 'request_type' rule in $allRules will catch it.

        $validatedData = $request->validate($allRules);

        // Flash all validated data to the session for the confirmation page
        session()->flash('form_data_for_confirmation', $validatedData);

        return redirect()->route('request.show_confirmation_page');
    }

    /**
     * Display the confirmation page with form data.
     */
    public function showConfirmationPage(): View|RedirectResponse
    {
        $formData = session()->get('form_data_for_confirmation');

        if (!$formData) {
            // If no data, redirect back to create form, perhaps with an error.
            return redirect()->route('request.create')->with('error', 'No data to confirm. Please submit the form again.');
        }

        // To display department names instead of IDs
        $departments = Department::orderBy('dept_name')->get()->keyBy('department_id');
        $user = Auth::user();
        $fromDepartmentName = $user->department ? $user->department->dept_name : 'N/A';
        
        // Re-flash the data so it's available if the user navigates away and comes back (e.g. refresh)
        // Or if they go back to edit and resubmit.
        session()->flash('form_data_for_confirmation', $formData); // Re-flash for current confirmation page access

        return view('requests.confirm', compact('formData', 'departments', 'fromDepartmentName', 'user'));
    }

    /**
     * Handle user going back to edit form from confirmation page.
     */
    public function editBeforeConfirmation(): RedirectResponse
    {
        $formDataFromConfirmation = session()->get('form_data_for_confirmation');
        if ($formDataFromConfirmation) {
            session()->flash('form_data_for_confirmation_edit', $formDataFromConfirmation);
        }
        // Keep the original confirmation data in case user navigates back to confirm page via browser back button
        // The showConfirmationPage will re-flash it anyway if it exists.
        // No, explicitly remove it, so if they submit create form again, it generates fresh confirmation data.
        // session()->forget('form_data_for_confirmation');
        // Let's stick to re-flashing the main one from showConfirmationPage and edit one from here.
        // The main concern is that `form_data_for_confirmation` shouldn't persist if they start a *new* form from scratch later.
        // Flashing takes care of this (available for one next request).

        return redirect()->route('request.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'request_type' => ['required', 'string', Rule::in(['IOM', 'Leave'])],
        ]);

        $requestType = $validatedData['request_type'];
        $user = Auth::user();

        $fromDepartmentId = $user->department_id;

        try {
            DB::beginTransaction();

            $formRequest = new FormRequest();
            $formRequest->form_type = $requestType;
            $formRequest->requested_by = $user->accnt_id;
            $formRequest->from_department_id = $fromDepartmentId;

            if ($requestType === 'IOM') {
                $iomValidatedData = $request->validate([
                    'iom_to_department_id' => 'required|integer|exists:tb_department,department_id',
                    'iom_re' => 'required|string|max:255',
                    'iom_priority' => ['required', Rule::in(['Routine', 'Urgent', 'Rush'])],
                    'iom_purpose' => ['required', Rule::in(['For Information', 'For Action', 'For Signature', 'For Comments', 'For Approval', 'Request'])],
                    'iom_specific_request_type' => [Rule::requiredIf($request->input('iom_purpose') === 'Request'), 'nullable', 'string', 'max:255', Rule::in(['Request for Facilities', 'Request for Computer Laboratory', 'Request for Venue'])],
                    'iom_description' => 'required|string|max:5000',
                    'iom_date_needed' => 'required|date|after:today',
                ]);

                $formRequest->title = $iomValidatedData['iom_re'];
                $formRequest->to_department_id = $iomValidatedData['iom_to_department_id'];
                $formRequest->date_submitted = now();

                // Determine the first approver based on user's role
                if ($user->accessRole === 'Requester') {
                    // If requester is staff, route to their department head first
                    $departmentHead = User::where('department_id', $user->department_id)
                                        ->where('position', 'Head')
                                        ->where('accessRole', 'Approver')
                                        ->first();
                    
                    if (!$departmentHead) {
                        DB::rollBack();
                        return redirect()->back()->with('error', 'Department head not found. Please contact your administrator.')->withInput();
                    }

                    $formRequest->status = 'Pending Department Head Approval';
                    $formRequest->current_approver_id = $departmentHead->accnt_id;
                } else if ($user->accessRole === 'Approver' && $user->position === 'Head') {
                    // If requester is a head, determine if sending to own department or other
                    if ($formRequest->to_department_id === $user->department_id) {
                        // Head sending to own department - auto-approve
                        $formRequest->status = 'Approved';
                        $formRequest->current_approver_id = null;
                    } else {
                        // Head sending to different department - auto-note and route to target department head
                        $targetDepartmentHead = User::where('department_id', $formRequest->to_department_id)
                                                ->where('position', 'Head')
                                                ->where('accessRole', 'Approver')
                                                ->first();

                        if (!$targetDepartmentHead) {
                            DB::rollBack();
                            return redirect()->back()->with('error', 'Target department head not found. Request cannot be submitted.')->withInput();
                        }

                        if ($targetDepartmentHead->accnt_id === $user->accnt_id) {
                            // If sender is also the head of target department
                            $formRequest->status = 'Approved';
                            $formRequest->current_approver_id = null;
                        } else {
                            // Auto-note and set status to In Progress
                            $formRequest->status = 'In Progress';
                            $formRequest->current_approver_id = $targetDepartmentHead->accnt_id;
                        }
                    }
                }

                $formRequest->save();

                // Create IOM details
                IomDetail::create([
                    'form_id' => $formRequest->form_id,
                    'date_needed' => $iomValidatedData['iom_date_needed'],
                    'priority' => $iomValidatedData['iom_priority'],
                    'purpose' => $iomValidatedData['iom_purpose'] . 
                        ($iomValidatedData['iom_purpose'] === 'Request' && !empty($iomValidatedData['iom_specific_request_type']) 
                            ? ' - ' . $iomValidatedData['iom_specific_request_type'] 
                            : ''),
                    'body' => $iomValidatedData['iom_description'],
                ]);

                // Only create Submitted record if not a department head
                if (!($user->accessRole === 'Approver' && $user->position === 'Head')) {
                    FormApproval::create([
                        'form_id' => $formRequest->form_id,
                        'approver_id' => $user->accnt_id,
                        'action' => 'Submitted',
                        'action_date' => now()
                    ]);
                }

                // If department head is submitting, auto-create note approval
                if ($user->accessRole === 'Approver' && $user->position === 'Head' && 
                    $formRequest->to_department_id !== $user->department_id) {
                    FormApproval::create([
                        'form_id' => $formRequest->form_id,
                        'approver_id' => $user->accnt_id,
                        'action' => 'Noted',
                        'action_date' => now()
                    ]);
                }

                $successMessage = 'IOM Request submitted successfully!';

            } elseif ($requestType === 'Leave') {
                $leaveValidatedData = $request->validate([
                    'leave_type' => ['required', Rule::in(['sick', 'vacation', 'emergency'])],
                    'date_of_leave' => 'required|date|after_or_equal:today',
                    'leave_description' => 'required|string|max:1000',
                ]);

                $formRequest->title = 'Leave Request - ' . ucfirst($leaveValidatedData['leave_type']);
                
                // All leave requests go to HR Department Head
                $hrDepartment = Department::where('dept_code', 'HR')->first();
                if (!$hrDepartment) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'HR Department not found. Cannot submit leave request.')->withInput();
                }
                $hrDepartmentHead = User::where('department_id', $hrDepartment->department_id)
                                        ->where('position', 'Head')
                                        ->first();
                if (!$hrDepartmentHead) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'HR Department Head not found. Cannot submit leave request.')->withInput();
                }
                $formRequest->current_approver_id = $hrDepartmentHead->accnt_id;
                $formRequest->status = 'Pending';
                $formRequest->save();

                LeaveDetail::create([
                    'form_id' => $formRequest->form_id,
                    'leave_type' => $leaveValidatedData['leave_type'],
                    'date_of_leave' => $leaveValidatedData['date_of_leave'],
                    'description' => $leaveValidatedData['leave_description'],
                ]);
                $successMessage = 'Leave Request submitted successfully!';
            } else {
                DB::rollBack();
                return redirect()->back()->withErrors(['request_type' => 'Invalid request type selected.'])->withInput();
            }

            DB::commit();
            return redirect()->route('dashboard')->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again. Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Track a specific request and show its details.
     */
    public function track($formId): View
    {
        $formRequest = FormRequest::with([
            'requester.department',
            'fromDepartment',
            'toDepartment',
            'iomDetails',
            'leaveDetails',
            'approvals.approver',
            'currentApprover'
        ])->findOrFail($formId);

        // Check if user has permission to view this request
        if (Auth::id() !== $formRequest->requested_by && 
            Auth::user()->accessRole !== 'Approver') {
            abort(403, 'You do not have permission to view this request.');
        }

        return view('requests.track', compact('formRequest'));
    }

    /**
     * Display a printable view of a completed request.
     */
    public function printView($formId): View
    {
        $formRequest = FormRequest::with([
            'requester.department',
            'fromDepartment',
            'toDepartment',
            'iomDetails',
            'leaveDetails',
            'approvals.approver.employeeInfo',
            'currentApprover'
        ])->findOrFail($formId);

        // Check if request is completed and user has permission to view
        if ($formRequest->status !== 'Approved') {
            abort(403, 'Only completed requests can be printed.');
        }

        if (Auth::id() !== $formRequest->requested_by && 
            Auth::user()->accessRole !== 'Approver') {
            abort(403, 'You do not have permission to view this request.');
        }

        return view('requests.print', compact('formRequest'));
    }

    // ... (show, edit, update, destroy methods can be added later)
}
