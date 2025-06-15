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
use Carbon\Carbon; // Ensure Carbon is imported
use Illuminate\Support\Facades\Log; // Add Log facade

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
        $todayPHT = now()->tz(config('app.timezone'))->toDateString(); // Get current date in PHT
        return view('requests.create', compact('departments', 'formData', 'todayPHT'));
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
                'iom_purpose' => ['required', Rule::in(['For Information', 'For Action', 'For Signature', 'For Comments', 'For Approval', 'Request', 'Others'])],
                'iom_specific_request_type' => [
                    Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Request'),
                    'nullable',
                    'string',
                    'max:255',
                    Rule::in(['Request for Facilities', 'Request for Computer Laboratory', 'Request for Venue'])
                ],
                'iom_other_purpose' => [
                    Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Others'),
                    'nullable',
                    'string',
                    'max:255'
                ],
                'iom_description' => 'required|string|max:5000',
                'iom_date_needed' => 'nullable|date|after_or_equal:today',
            ]);
        } elseif ($requestType === 'Leave') {
            $allRules = array_merge($allRules, [
                'leave_type' => ['required', Rule::in(['sick', 'vacation', 'emergency'])],
                'leave_start_date' => 'required|date|after_or_equal:today', // Ensure this is after_or_equal:today
                'leave_end_date' => 'required|date|after_or_equal:leave_start_date',
                'leave_days' => 'required|integer|min:1',
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
        $formData = session('form_data_for_confirmation');

        if (!$formData) {
            return redirect()->route('request.create')
                ->with('error', 'No request data found. Please submit the form again.');
        }

        // Get the user's role and position
        $user = Auth::user();
        $isDepartmentHead = $user->accessRole === 'Approver' && $user->position === 'Head';

        // To display department names instead of IDs
        $departments = Department::orderBy('dept_name')->get()->keyBy('department_id');
        $fromDepartmentName = $user->department ? $user->department->dept_name : 'N/A';

        // Re-flash the data for the next request
        session()->flash('form_data_for_confirmation', $formData);

        // If it's a Department Head submitting an IOM, use the special confirmation page
        if ($isDepartmentHead && $formData['request_type'] === 'IOM') {
            return view('requests.confirm-department-head-iom', compact('formData'));
        }

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
                    'iom_purpose' => ['required', Rule::in(['For Information', 'For Action', 'For Signature', 'For Comments', 'For Approval', 'Request', 'Others'])],
                    'iom_specific_request_type' => [
                        Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Request'),
                        'nullable',
                        'string',
                        'max:255',
                        Rule::in(['Request for Facilities', 'Request for Computer Laboratory', 'Request for Venue'])
                    ],
                    'iom_other_purpose' => [
                        Rule::requiredIf(fn() => $request->input('iom_purpose') === 'Others'),
                        'nullable',
                        'string',
                        'max:255'
                    ],
                    'iom_description' => 'required|string|max:5000',
                    'iom_date_needed' => 'required|date|after_or_equal:today', // Changed from after:today
                ]);

                $formRequest->title = $iomValidatedData['iom_re'];
                $formRequest->to_department_id = $iomValidatedData['iom_to_department_id'];
                $formRequest->date_submitted = now();

                // Save the form request first to get the form_id
                $formRequest->save();

                // Create IOM details
                IomDetail::create([
                    'form_id' => $formRequest->form_id,
                    'date_needed' => $iomValidatedData['iom_date_needed'],
                    'priority' => $iomValidatedData['iom_priority'],
                    'purpose' => $this->formatIOMPurpose($iomValidatedData),
                    'body' => $iomValidatedData['iom_description'],
                ]);

                // Handle routing based on user role
                if ($user->accessRole === 'Approver' && $user->position === 'Head') {
                    Log::info('[Dept Head IOM] Processing IOM submission by Department Head.', ['user_id' => $user->accnt_id, 'to_department_id' => $formRequest->to_department_id]);
                    // Department Head is creating the request
                    if ($formRequest->to_department_id === $user->department_id) {
                        // If sending to own department, auto-approve
                        Log::info('[Dept Head IOM] Sending to own department. Auto-approving.', ['department_id' => $user->department_id]);
                        $formRequest->status = 'Approved';
                        $formRequest->current_approver_id = null;

                        // Create auto-approval record
                        FormApproval::create([
                            'form_id' => $formRequest->form_id,
                            'approver_id' => $user->accnt_id,
                            'action' => 'Approved',
                            'action_date' => now()
                        ]);
                    } else {
                        // If sending to different department, auto-note and route to target
                        Log::info('[Dept Head IOM] Sending to a different department.', ['target_department_id' => $formRequest->to_department_id]);
                        $vpaaDepartment = Department::where('dept_code', 'VPAA')
                            ->orWhere('dept_name', 'Vice President for Academic Affairs')
                            ->first();

                        $targetApprover = null;
                        Log::debug('[Dept Head IOM] Determining target approver.', [
                            'from_user_id' => $user->accnt_id,
                            'to_department_id' => $formRequest->to_department_id,
                            'vpaa_dept_obj_found' => !is_null($vpaaDepartment),
                            'vpaa_dept_id_if_found' => $vpaaDepartment ? $vpaaDepartment->department_id : null
                        ]);

                        if ($vpaaDepartment && $formRequest->to_department_id == $vpaaDepartment->department_id) {
                            Log::info('[Dept Head IOM] Target department is VPAA. Looking for user with VPAA position.', ['target_dept_id' => $formRequest->to_department_id]);
                            $targetApprover = User::where('department_id', $formRequest->to_department_id)
                                ->where('position', 'VPAA')
                                ->where('accessRole', 'Approver')
                                ->first();

                            if (!$targetApprover) {
                                Log::error('[Dept Head IOM] User with VPAA position not found in VPAA department.', ['target_dept_id' => $formRequest->to_department_id]);
                                DB::rollBack();
                                return redirect()->back()->with('error', 'User with VPAA position not found in the VPAA department. Request cannot be submitted.')->withInput();
                            }
                            Log::info('[Dept Head IOM] VPAA position user found for routing.', ['approver_id' => $targetApprover->accnt_id]);
                        } else {
                            Log::info('[Dept Head IOM] Target is not VPAA (or VPAA dept not identified as target). Looking for user with Head position.', ['target_dept_id' => $formRequest->to_department_id]);
                            $targetApprover = User::where('department_id', $formRequest->to_department_id)
                                ->where('position', 'Head')
                                ->where('accessRole', 'Approver')
                                ->first();

                            if (!$targetApprover) {
                                Log::error('[Dept Head IOM] User with Head position not found in target department.', ['target_dept_id' => $formRequest->to_department_id]);
                                DB::rollBack();
                                return redirect()->back()->with('error', 'Target department head not found. Request cannot be submitted.')->withInput();
                            }
                            Log::info('[Dept Head IOM] Head position user found for routing.', ['approver_id' => $targetApprover->accnt_id]);
                        }

                        // Set status to In Progress and route to target department
                        $formRequest->status = 'In Progress';
                        $formRequest->current_approver_id = $targetApprover->accnt_id;

                        // Create auto-note record with signature
                        FormApproval::create([
                            'form_id' => $formRequest->form_id,
                            'approver_id' => $user->accnt_id,
                            'action' => 'Noted',
                            'action_date' => now(),
                            'signature_data' => $request->signature ?? null,
                            'signature_name' => $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName
                        ]);
                        Log::info('[Dept Head IOM] Successfully routed to target approver.', ['approver_id' => $targetApprover->accnt_id, 'status' => $formRequest->status]);
                    }
                } else {
                    // Regular staff is creating the request
                    Log::info('[Regular Staff IOM] Processing IOM submission by regular staff.', [
                        'user_id' => $user->accnt_id,
                        'user_department_id' => $user->department_id,
                        'form_to_department_id' => $formRequest->to_department_id // This is the ultimate destination
                    ]);

                    // Staff's IOM always goes to their own department head first for noting.
                    $departmentHead = User::where('department_id', $user->department_id)
                        ->where('position', 'Head')
                        ->where('accessRole', 'Approver')
                        ->first();

                    if (!$departmentHead) {
                        Log::error('[Regular Staff IOM] Submitting user\'s own department head not found. Cannot initiate IOM.', [
                            'user_id' => $user->accnt_id,
                            'user_department_id' => $user->department_id
                        ]);
                        DB::rollBack();
                        return redirect()->back()->with('error', 'Your department head could not be found. The request cannot be submitted. Please contact an administrator.')->withInput();
                    }

                    $formRequest->current_approver_id = $departmentHead->accnt_id;
                    $formRequest->status = 'Pending'; // Initial status, awaiting action from their own Head.

                    Log::info('[Regular Staff IOM] IOM routed to user\'s own department head for initial action.', [
                        'form_id' => $formRequest->form_id,
                        'current_approver_id' => $departmentHead->accnt_id,
                        'status' => $formRequest->status,
                        'final_target_department_id' => $formRequest->to_department_id // Log the final destination for clarity
                    ]);

                    // Create a "Submitted" record in FormApprovals by the requester
                    FormApproval::create([
                        'form_id' => $formRequest->form_id,
                        'approver_id' => $user->accnt_id, // This is the staff member who created the request
                        'action' => 'Submitted',
                        'action_date' => now(),
                    ]);
                    Log::info('[Regular Staff IOM] "Submitted" record created for IOM.', ['form_id' => $formRequest->form_id, 'submitted_by_user_id' => $user->accnt_id]);
                }

                $formRequest->save();
                $successMessage = 'IOM Request submitted successfully!';

            } elseif ($requestType === 'Leave') {
                $leaveValidatedData = $request->validate([
                    'leave_type' => ['required', Rule::in(['sick', 'vacation', 'emergency'])],
                    'leave_start_date' => 'required|date|after_or_equal:today',
                    'leave_end_date' => 'required|date|after_or_equal:leave_start_date',
                    'leave_days' => 'required|integer|min:1',
                    'leave_description' => 'required|string|max:1000',
                ]);

                $formRequest->title = 'Leave Request - ' . ucfirst($leaveValidatedData['leave_type']);
                $formRequest->date_submitted = now();

                // Save the form request first to get the form_id
                $formRequest->save();

                // Create leave details
                LeaveDetail::create([
                    'form_id' => $formRequest->form_id,
                    'leave_type' => $leaveValidatedData['leave_type'],
                    'start_date' => $leaveValidatedData['leave_start_date'],
                    'end_date' => $leaveValidatedData['leave_end_date'],
                    'days' => $leaveValidatedData['leave_days'],
                    'description' => $leaveValidatedData['leave_description'],
                ]);

                if ($user->accessRole === 'Approver' && $user->position === 'Head') {
                    // Department Head is creating a Leave request.
                    // New logic: Route to VPAA first.
                    $vpaaDepartment = Department::where('dept_code', 'VPAA')
                        ->orWhere('dept_name', 'Vice President for Academic Affairs')
                        ->first();
                    $vpaaUser = null;
                    if ($vpaaDepartment) {
                        $vpaaUser = User::where('department_id', $vpaaDepartment->department_id)
                            ->where('position', 'VPAA')
                            ->where('accessRole', 'Approver')
                            ->first();
                    }

                    if ($vpaaUser) {
                        // VPAA found, route to VPAA
                        $formRequest->status = 'Pending'; // Pending VPAA "Noted"
                        $formRequest->current_approver_id = $vpaaUser->accnt_id;
                        $formRequest->to_department_id = $vpaaUser->department_id;

                        // Create 'Submitted' record for the requesting Department Head
                        FormApproval::create([
                            'form_id' => $formRequest->form_id,
                            'approver_id' => $user->accnt_id, // The requesting Dept Head
                            'action' => 'Submitted',
                            'action_date' => now(),
                        ]);
                    } else {
                        // VPAA not found, fallback to HR
                        Log::warning('VPAA user or department not found for routing Department Head Leave request. Falling back to HR.', [
                            'form_id' => $formRequest->form_id,
                            'requesting_user_id' => $user->accnt_id
                        ]);

                        $hrDepartment = Department::where('dept_code', 'HR')
                            ->orWhere('dept_code', 'HRD')
                            ->orWhere('dept_code', 'HRMD')
                            ->orWhere('dept_name', 'like', '%Human Resource%')
                            ->first();

                        if (!$hrDepartment) {
                            DB::rollBack();
                            return redirect()->back()->with('error', 'HR Department not found for fallback routing. Please contact your administrator.')->withInput();
                        }

                        $hrApprover = User::where('department_id', $hrDepartment->department_id)
                            ->where('position', 'Head')
                            ->where('accessRole', 'Approver')
                            ->first();

                        if (!$hrApprover) {
                            DB::rollBack();
                            return redirect()->back()->with('error', 'HR Approver not found for fallback routing. Please contact your administrator.')->withInput();
                        }

                        // Set status and route to HR
                        $formRequest->status = 'In Progress'; // "In Progress" for HR
                        $formRequest->current_approver_id = $hrApprover->accnt_id;
                        $formRequest->to_department_id = $hrDepartment->department_id;

                        // Create auto-note record for the requesting Department Head (self-noting)
                        FormApproval::create([
                            'form_id' => $formRequest->form_id,
                            'approver_id' => $user->accnt_id,
                            'action' => 'Noted',
                            'action_date' => now(),
                            'signature_data' => $request->signature ?? null, // Assuming signature might be part of form
                            'signature_name' => $user->employeeInfo->FirstName . ' ' . $user->employeeInfo->LastName
                        ]);
                    }
                } else {
                    // Regular staff is creating the request
                    $departmentHead = User::where('department_id', $user->department_id)
                        ->where('position', 'Head')
                        ->where('accessRole', 'Approver')
                        ->first();

                    if (!$departmentHead) {
                        DB::rollBack();
                        return redirect()->back()->with('error', 'Department Head not found. Please contact your administrator.')->withInput();
                    }

                    // Set initial status and route to department head
                    $formRequest->status = 'Pending';
                    $formRequest->current_approver_id = $departmentHead->accnt_id;

                    // Create submission record
                    FormApproval::create([
                        'form_id' => $formRequest->form_id,
                        'approver_id' => $user->accnt_id,
                        'action' => 'Submitted',
                        'action_date' => now()
                    ]);
                }

                $formRequest->save();
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
            return redirect()->back()->with('error', 'An error occurred while submitting your request. Please try again.')->withInput();
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
        if (
            Auth::id() !== $formRequest->requested_by &&
            Auth::user()->accessRole !== 'Approver'
        ) {
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

        if (
            Auth::id() !== $formRequest->requested_by &&
            Auth::user()->accessRole !== 'Approver'
        ) {
            abort(403, 'You do not have permission to view this request.');
        }

        return view('requests.print', compact('formRequest'));
    }

    private function formatIOMPurpose(array $data): string
    {
        $purpose = $data['iom_purpose'];
        if ($purpose === 'Request' && !empty($data['iom_specific_request_type'])) {
            $purpose .= ' - ' . $data['iom_specific_request_type'];
        } elseif ($purpose === 'Others' && !empty($data['iom_other_purpose'])) {
            $purpose .= ': ' . $data['iom_other_purpose'];
        }
        return $purpose;
    }

    // ... (show, edit, update, destroy methods can be added later)
}
