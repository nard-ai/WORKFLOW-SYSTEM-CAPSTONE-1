<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Submit Form') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('request.store') }}" id="createRequestForm">
                        @csrf

                        <!-- Request Type -->
                        <div class="mb-6">
                            <x-input-label for="request_type" :value="__('Form Type')" />
                            <select id="request_type" name="request_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm bg-white dark:bg-gray-900" required>
                                <option value="">-- Select Form Type --</option>
                                <option value="IOM" {{ old('request_type', $formData['request_type'] ?? '') == 'IOM' ? 'selected' : '' }}>Inter-Office Memorandum (IOM)</option>
                                <option value="Leave" {{ old('request_type', $formData['request_type'] ?? '') == 'Leave' ? 'selected' : '' }}>Leave</option>
                            </select>
                            <x-input-error :messages="$errors->get('request_type')" class="mt-2" />
                        </div>

                        {{-- IOM Specific Fields --}}
                        <div id="iom_fields" class="iom-fields-class specific-fields hidden">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 border-b pb-2">IOM Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                                <!-- IOM To (Department) - Autocomplete -->
                                <div class="mb-4">
                                    <x-input-label for="iom_to_department_name_display" :value="__('To (Department)')" />
                                    <x-text-input id="iom_to_department_name_display"
                                                class="block mt-1 w-full bg-white dark:bg-gray-900"
                                                type="text"
                                                name="iom_to_department_name_display"
                                                :value="old('iom_to_department_name_display', $formData['iom_to_department_name_display'] ?? '')"
                                                list="department_list_datalist" />
                                    <datalist id="department_list_datalist">
                                        @foreach($departments as $department)
                                            <option value="{{ $department->dept_name }} ({{ $department->dept_code }})" data-id="{{ $department->department_id }}"></option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="iom_to_department_id" id="iom_to_department_id" value="{{ old('iom_to_department_id', $formData['iom_to_department_id'] ?? '') }}">
                                    <x-input-error :messages="$errors->get('iom_to_department_id')" class="mt-2" />
                                </div>

                                <!-- IOM Re (Subject) -->
                                <div class="mb-4">
                                    <x-input-label for="iom_re" :value="__('Re (Subject)')" />
                                    <x-text-input id="iom_re" class="block mt-1 w-full" type="text" name="iom_re" :value="old('iom_re', $formData['iom_re'] ?? '')" />
                                    <x-input-error :messages="$errors->get('iom_re')" class="mt-2" />
                                </div>

                                <!-- IOM From -->
                                <div class="mb-4">
                                    <x-input-label for="iom_from" :value="__('From (Your Department)')" />
                                    <x-text-input id="iom_from" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" name="iom_from" :value="old('iom_from', $formData['iom_from'] ?? (Auth::user()->department->dept_name ?? (Auth::user()->username ?? 'Unknown User')))" readonly />
                                </div>
                                
                                <!-- IOM Date Needed -->
                                <div class="mb-4">
                                    <x-input-label for="iom_date_needed" :value="__('Date Needed')" />
                                    <x-text-input id="iom_date_needed" class="block mt-1 w-full" type="date" name="iom_date_needed" :value="old('iom_date_needed', $formData['iom_date_needed'] ?? '')" required />
                                    <x-input-error :messages="$errors->get('iom_date_needed')" class="mt-2" />
                                </div>
                                
                                <div class="mb-4">
                                    <x-input-label for="iom_priority" :value="__('Priority')" />
                                    <select id="iom_priority" name="iom_priority" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="Routine" {{ old('iom_priority', $formData['iom_priority'] ?? '') == 'Routine' ? 'selected' : '' }}>Routine</option>
                                        <option value="Urgent" {{ old('iom_priority', $formData['iom_priority'] ?? '') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="Rush" {{ old('iom_priority', $formData['iom_priority'] ?? '') == 'Rush' ? 'selected' : '' }}>Rush</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('iom_priority')" class="mt-2" />
                                </div>
                                <div class="mb-4">
                                    <x-input-label for="iom_purpose" :value="__('Purpose')" />
                                    <select id="iom_purpose" name="iom_purpose" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="For Information" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Information' ? 'selected' : '' }}>For your information</option>
                                        <option value="For Action" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Action' ? 'selected' : '' }}>For your action</option>
                                        <option value="For Signature" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Signature' ? 'selected' : '' }}>For your signature</option>
                                        <option value="For Comments" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Comments' ? 'selected' : '' }}>For comments</option>
                                        <option value="For Approval" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'For Approval' ? 'selected' : '' }}>For approval</option>
                                        <option value="Request" {{ old('iom_purpose', $formData['iom_purpose'] ?? '') == 'Request' ? 'selected' : '' }}>Request</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('iom_purpose')" class="mt-2" />
                                </div>
                            </div>
                             <!-- IOM Specific Request Type (conditionally shown) -->
                            <div class="mb-4 md:col-span-2 hidden" id="iom_specific_request_type_container">
                                <x-input-label for="iom_specific_request_type" :value="__('Specific Request Type (if Purpose is Request)')" />
                                <select id="iom_specific_request_type" name="iom_specific_request_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">-- Select Specific Request --</option>
                                    <option value="Request for Facilities" {{ old('iom_specific_request_type', $formData['iom_specific_request_type'] ?? '') == 'Request for Facilities' ? 'selected' : '' }}>Request for Facilities</option>
                                    <option value="Request for Computer Laboratory" {{ old('iom_specific_request_type', $formData['iom_specific_request_type'] ?? '') == 'Request for Computer Laboratory' ? 'selected' : '' }}>Request for Computer Laboratory</option>
                                    <option value="Request for Venue" {{ old('iom_specific_request_type', $formData['iom_specific_request_type'] ?? '') == 'Request for Venue' ? 'selected' : '' }}>Request for Venue</option>
                                </select>
                                <x-input-error :messages="$errors->get('iom_specific_request_type')" class="mt-2" />
                            </div>


                            <!-- IOM Description -->
                            <div class="mb-4 md:col-span-2">
                                <x-input-label for="iom_description" :value="__('Description/Body')" />
                                <textarea id="iom_description" name="iom_description" rows="5" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('iom_description', $formData['iom_description'] ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('iom_description')" class="mt-2" />
                            </div>
                        </div>
                        
                        {{-- Leave Request Specific Fields --}}
                        <div id="leave_fields" class="leave-fields-class specific-fields hidden">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 border-b pb-2">Leave Details</h3>
                            
                            <!-- Leave Type -->
                            <div class="mb-6">
                                <x-input-label :value="__('Type of Leave')" class="mb-2 font-medium"/>
                                <div class="flex flex-col sm:flex-row sm:gap-x-6 gap-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-blue-600 focus:ring-blue-500" name="leave_type" value="sick" {{ old('leave_type', $formData['leave_type'] ?? '') == 'sick' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Sick Leave') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-blue-600 focus:ring-blue-500" name="leave_type" value="vacation" {{ old('leave_type', $formData['leave_type'] ?? '') == 'vacation' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Vacation Leave') }}</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" class="form-radio text-blue-600 focus:ring-blue-500" name="leave_type" value="emergency" {{ old('leave_type', $formData['leave_type'] ?? '') == 'emergency' ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Emergency Leave') }}</span>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('leave_type')" class="mt-2" />
                            </div>

                            <!-- Date of Leave -->
                            <div class="mb-6">
                                <x-input-label for="date_of_leave" :value="__('Date of Leave')" />
                                <x-text-input id="date_of_leave" class="block mt-1 w-full" type="date" name="date_of_leave" :value="old('date_of_leave', $formData['date_of_leave'] ?? '')" />
                                <x-input-error :messages="$errors->get('date_of_leave')" class="mt-2" />
                            </div>

                            <!-- Leave Description/Reason -->
                            <div class="mb-6">
                                <x-input-label for="leave_description" :value="__('Description / Reason')" />
                                <textarea id="leave_description" name="leave_description" rows="4" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 text-sm placeholder-gray-400" placeholder="Provide a brief reason for your leave...">{{ old('leave_description', $formData['leave_description'] ?? '') }}</textarea>
                                <x-input-error :messages="$errors->get('leave_description')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 border-t pt-6">
                            <x-primary-button type="button" id="reviewButton">
                                {{ __('Submit') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Confirmation Modal -->
                    <div id="confirmationModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden" style="z-index: 50;">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirmation</h3>
                                
                                <div id="confirmationContent" class="space-y-4">
                                    <!-- Content will be populated by JavaScript -->
                                </div>

                                <div class="mt-6 flex justify-end space-x-4 border-t pt-4">
                                    <button type="button" id="editButton" class="text-sm text-gray-600 hover:text-gray-900 font-medium">
                                        Back
                                    </button>
                                    <x-primary-button id="confirmSubmitButton">
                                        Confirm & Submit
                                    </x-primary-button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const form = document.getElementById('createRequestForm');
                            const requestTypeSelect = document.getElementById('request_type');
                            const iomFields = document.getElementById('iom_fields');
                            const leaveFields = document.getElementById('leave_fields');
                            const reviewButton = document.getElementById('reviewButton');
                            const modal = document.getElementById('confirmationModal');
                            const confirmationContent = document.getElementById('confirmationContent');
                            const editButton = document.getElementById('editButton');
                            const confirmSubmitButton = document.getElementById('confirmSubmitButton');
                            const dateNeededInput = document.getElementById('iom_date_needed');

                            const iomPurposeSelect = document.getElementById('iom_purpose');
                            const iomSpecificRequestTypeContainer = document.getElementById('iom_specific_request_type_container');
                            const iomSpecificRequestTypeSelect = document.getElementById('iom_specific_request_type');


                            // For IOM department autocomplete
                            const iomToDepartmentNameDisplay = document.getElementById('iom_to_department_name_display');
                            const iomToDepartmentIdHidden = document.getElementById('iom_to_department_id');
                            const departmentDatalist = document.getElementById('department_list_datalist');

                            // Required attributes map for conditional validation (client-side indication)
                            // Note: Server-side validation is the source of truth.
                            const iomRequiredFields = ['iom_to_department_name_display', 'iom_re', 'iom_priority', 'iom_purpose', 'iom_description', 'iom_date_needed'];
                            const leaveRequiredFields = ['date_of_leave', 'leave_description']; 

                            function toggleSpecificRequestTypeField() {
                                if (iomPurposeSelect && iomSpecificRequestTypeContainer && iomSpecificRequestTypeSelect) {
                                    if (iomPurposeSelect.value === 'Request') {
                                        iomSpecificRequestTypeContainer.classList.remove('hidden');
                                        iomSpecificRequestTypeSelect.setAttribute('required', 'required');
                                    } else {
                                        iomSpecificRequestTypeContainer.classList.add('hidden');
                                        iomSpecificRequestTypeSelect.removeAttribute('required');
                                        iomSpecificRequestTypeSelect.value = ''; // Clear value when hidden
                                    }
                                }
                            }
                            
                            function setInitialToDepartmentDisplay() {
                                const initialDeptId = iomToDepartmentIdHidden.value;
                                if (initialDeptId && departmentDatalist && departmentDatalist.options) {
                                    for (let i = 0; i < departmentDatalist.options.length; i++) {
                                        const option = departmentDatalist.options[i];
                                        if (option.getAttribute('data-id') === initialDeptId) {
                                            iomToDepartmentNameDisplay.value = option.value;
                                            break;
                                        }
                                    }
                                }
                            }


                            function toggleFields() {
                                const selectedType = requestTypeSelect.value;
                                
                                iomFields.classList.add('hidden');
                                leaveFields.classList.add('hidden');
                                reviewButton.disabled = selectedType === '' || selectedType === null;

                                document.querySelectorAll('.specific-fields [required]').forEach(el => el.removeAttribute('required'));
                                 if (iomSpecificRequestTypeSelect) iomSpecificRequestTypeSelect.removeAttribute('required');


                                if (selectedType === 'IOM') {
                                    iomFields.classList.remove('hidden');
                                    iomRequiredFields.forEach(id => {
                                        const el = document.getElementById(id);
                                        if(el) el.setAttribute('required', 'required');
                                    });
                                    toggleSpecificRequestTypeField(); // Also check this on type change
                                } else if (selectedType === 'Leave') {
                                    leaveFields.classList.remove('hidden');
                                    leaveRequiredFields.forEach(id => {
                                        const el = document.getElementById(id);
                                        if(el) el.setAttribute('required', 'required');
                                    });
                                    const firstLeaveTypeRadio = document.querySelector('input[name="leave_type"]');
                                    if(firstLeaveTypeRadio) firstLeaveTypeRadio.setAttribute('required', 'required'); // One radio must be selected
                                }
                            }

                            requestTypeSelect.addEventListener('change', toggleFields);
                            if (iomPurposeSelect) {
                                iomPurposeSelect.addEventListener('change', toggleSpecificRequestTypeField);
                            }
                            
                            setInitialToDepartmentDisplay(); // Set display name if ID is pre-filled
                            toggleFields(); // Initial call based on old/formData

                            if (iomToDepartmentNameDisplay) {
                                iomToDepartmentNameDisplay.addEventListener('input', function() {
                                    const inputValue = this.value;
                                    let found = false;
                                    if (departmentDatalist && departmentDatalist.options) {
                                        for (let i = 0; i < departmentDatalist.options.length; i++) {
                                            const option = departmentDatalist.options[i];
                                            if (option.value === inputValue) {
                                                iomToDepartmentIdHidden.value = option.getAttribute('data-id');
                                                found = true;
                                                break;
                                            }
                                        }
                                    }
                                    if (!found) {
                                        iomToDepartmentIdHidden.value = ''; 
                                    }
                                });

                                // Clear hidden ID if display name is manually cleared
                                iomToDepartmentNameDisplay.addEventListener('change', function() { // Using 'change' to detect blur or selection from datalist
                                    if (this.value === '') {
                                        iomToDepartmentIdHidden.value = '';
                                    }
                                });
                            }

                            function formatDate(dateString) {
                                if (!dateString) return 'N/A';
                                return new Date(dateString).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric'
                                });
                            }

                            function showConfirmation() {
                                const formData = new FormData(form);
                                const requestType = formData.get('request_type');
                                let content = '';

                                content += `<div class="border rounded-lg p-4 dark:border-gray-700">
                                    <p class="font-medium mb-2">Form Type:</p>
                                    <p class="text-gray-800 dark:text-gray-200 mb-4 bg-indigo-50 dark:bg-indigo-900/30 p-2 rounded border-2 border-indigo-300 dark:border-indigo-600 shadow-sm">${requestType}</p>`;

                                if (requestType === 'IOM') {
                                    const dept = document.querySelector('#iom_to_department_name_display').value;
                                    content += `
                                        <p class="font-medium mb-2">To Department:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4 bg-indigo-50 dark:bg-indigo-900/30 p-2 rounded border-2 border-indigo-300 dark:border-indigo-600 shadow-sm">${dept}</p>
                                        <p class="font-medium mb-2">Subject:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formData.get('iom_re')}</p>
                                        <p class="font-medium mb-2">Priority:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formData.get('iom_priority')}</p>
                                        <p class="font-medium mb-2">Purpose:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formData.get('iom_purpose')}</p>
                                        <p class="font-medium mb-2">Date Needed:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formatDate(formData.get('iom_date_needed'))}</p>
                                        <p class="font-medium mb-2">Description:</p>
                                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">${formData.get('iom_description')}</p>`;
                                } else if (requestType === 'Leave') {
                                    content += `
                                        <p class="font-medium mb-2">Leave Type:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formData.get('leave_type')}</p>
                                        <p class="font-medium mb-2">Date of Leave:</p>
                                        <p class="text-gray-800 dark:text-gray-200 mb-4">${formatDate(formData.get('date_of_leave'))}</p>
                                        <p class="font-medium mb-2">Reason:</p>
                                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">${formData.get('leave_description')}</p>`;
                                }

                                content += '</div>';
                                confirmationContent.innerHTML = content;
                                modal.classList.remove('hidden');
                            }

                            // Add highlight functionality only for specific fields
                            function addHighlight(element) {
                                if (element) {
                                    element.style.transition = 'all 0.3s';
                                    element.style.boxShadow = '0 0 5px rgba(99, 102, 241, 0.5)';
                                    element.style.borderColor = '#6366f1';
                                }
                            }

                            function removeHighlight(element) {
                                if (element) {
                                    element.style.boxShadow = '';
                                    element.style.borderColor = '';
                                }
                            }

                            // Add event listeners for highlighting only Form Type and To Department
                            const formTypeSelect = document.getElementById('request_type');
                            const toDepartmentInput = document.getElementById('iom_to_department_name_display');

                            if (formTypeSelect) {
                                formTypeSelect.addEventListener('focus', () => addHighlight(formTypeSelect));
                                formTypeSelect.addEventListener('blur', () => removeHighlight(formTypeSelect));
                            }

                            if (toDepartmentInput) {
                                toDepartmentInput.addEventListener('focus', () => addHighlight(toDepartmentInput));
                                toDepartmentInput.addEventListener('blur', () => removeHighlight(toDepartmentInput));
                            }

                            reviewButton.addEventListener('click', function(e) {
                                e.preventDefault();
                                if (form.checkValidity()) {
                                    showConfirmation();
                                } else {
                                    form.reportValidity();
                                }
                            });

                            editButton.addEventListener('click', function() {
                                modal.classList.add('hidden');
                            });

                            // Close modal when clicking outside
                            modal.addEventListener('click', function(e) {
                                if (e.target === modal) {
                                    modal.classList.add('hidden');
                                }
                            });

                            confirmSubmitButton.addEventListener('click', function() {
                                form.submit();
                            });

                            // Set minimum date for Date Needed
                            function setMinimumDate() {
                                const tomorrow = new Date();
                                tomorrow.setDate(tomorrow.getDate() + 1);
                                const formattedDate = tomorrow.toISOString().split('T')[0];
                                if (dateNeededInput) {
                                    dateNeededInput.min = formattedDate;
                                    
                                    // If current value is before minimum date, clear it
                                    if (dateNeededInput.value && dateNeededInput.value <= formattedDate) {
                                        dateNeededInput.value = '';
                                    }
                                }
                            }

                            // Set initial minimum date
                            setMinimumDate();

                            // Update minimum date at midnight
                            setInterval(() => {
                                const now = new Date();
                                if (now.getHours() === 0 && now.getMinutes() === 0 && now.getSeconds() === 0) {
                                    setMinimumDate();
                                }
                            }, 1000);
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 