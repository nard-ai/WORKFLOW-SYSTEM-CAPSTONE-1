<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Approvers') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Department Staff</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Manage approver roles and permissions for your department staff members.
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Permissions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($employees as $employee)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $employee->employeeInfo->FirstName }} {{ $employee->employeeInfo->LastName }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $employee->position }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $employee->accessRole === 'Approver' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $employee->accessRole }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($employee->accessRole === 'Approver' && $employee->approverPermissions)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    <ul class="list-disc list-inside">
                                                        @if($employee->approverPermissions->can_approve_pending)
                                                            <li>Can approve Pending requests</li>
                                                        @endif
                                                        @if($employee->approverPermissions->can_approve_in_progress)
                                                            <li>Can approve In Progress requests</li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">No special permissions</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($employee->position !== 'Head')
                                                <form action="{{ route('approver-assignments.update', $employee) }}" method="POST" class="space-y-4">
                                                    @csrf
                                                    @method('PUT')
                                                    
                                                    <div class="flex items-center space-x-4">
                                                        <select name="accessRole" 
                                                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600"
                                                            onchange="togglePermissions(this)">
                                                            <option value="Viewer" {{ $employee->accessRole === 'Viewer' ? 'selected' : '' }}>Viewer</option>
                                                            <option value="Approver" {{ $employee->accessRole === 'Approver' ? 'selected' : '' }}>Approver</option>
                                                        </select>

                                                        <div class="permissions-options {{ $employee->accessRole !== 'Approver' ? 'hidden' : '' }} p-2 rounded">
                                                            <label class="inline-flex items-center">
                                                                <input type="hidden" name="can_approve_pending" value="0">
                                                                <input type="checkbox" 
                                                                    name="can_approve_pending" 
                                                                    value="1"
                                                                    class="permission-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                                    {{ $employee->approverPermissions?->can_approve_pending ? 'checked' : '' }}>
                                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Pending</span>
                                                            </label>

                                                            <label class="inline-flex items-center ml-4">
                                                                <input type="hidden" name="can_approve_in_progress" value="0">
                                                                <input type="checkbox" 
                                                                    name="can_approve_in_progress" 
                                                                    value="1"
                                                                    class="permission-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                                    {{ $employee->approverPermissions?->can_approve_in_progress ? 'checked' : '' }}>
                                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">In Progress</span>
                                                            </label>
                                                        </div>

                                                        <button type="submit" 
                                                            onclick="return validateAndConfirm(event, this.form)"
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Update
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Department Head (All permissions)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Confirmation Modal -->
    <div id="passwordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" 
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                    Confirm Password
                </h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Please enter your password to confirm this action.
                    </p>
                    <input type="password" 
                        id="confirmPassword" 
                        class="mt-4 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600"
                        placeholder="Enter your password">
                    <div id="passwordError" class="mt-2 text-sm text-red-600 dark:text-red-400 hidden">
                        Password is required
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" 
                        onclick="closePasswordModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancel
                    </button>
                    <button type="button" 
                        onclick="confirmPasswordAndSubmit()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentForm = null;

        function togglePermissions(select) {
            const permissionsDiv = select.parentElement.querySelector('.permissions-options');
            const checkboxes = permissionsDiv.querySelectorAll('.permission-checkbox');
            
            if (select.value === 'Approver') {
                permissionsDiv.classList.remove('hidden');
                // Don't uncheck boxes when switching back to Approver
            } else {
                permissionsDiv.classList.add('hidden');
                // Uncheck all checkboxes when switching to Viewer
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            }

            // Remove any existing error messages
            const errorDiv = permissionsDiv.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.remove();
            }
            permissionsDiv.classList.remove('border-red-500');
        }

        function validateAndConfirm(event, form) {
            event.preventDefault();
            
            // Reset error states
            const permissionsDiv = form.querySelector('.permissions-options');
            permissionsDiv.classList.remove('border-red-500');
            const errorDiv = permissionsDiv.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.remove();
            }

            // Check if role is Approver and validate checkbox selection
            const roleSelect = form.querySelector('select[name="accessRole"]');
            if (roleSelect.value === 'Approver') {
                const checkboxes = form.querySelectorAll('.permission-checkbox');
                const hasCheckedPermission = Array.from(checkboxes).some(checkbox => checkbox.checked);
                
                if (!hasCheckedPermission) {
                    // Add error message
                    permissionsDiv.classList.add('border-red-500');
                    const error = document.createElement('div');
                    error.className = 'error-message text-sm text-red-600 dark:text-red-400 mt-1';
                    error.textContent = 'Please select at least one permission';
                    permissionsDiv.appendChild(error);
                    return false;
                }
            }

            // Store the current form and show password modal
            currentForm = form;
            const passwordModal = document.getElementById('passwordModal');
            const passwordInput = document.getElementById('confirmPassword');
            const passwordError = document.getElementById('passwordError');
            
            passwordModal.classList.remove('hidden');
            passwordInput.value = '';
            passwordError.classList.add('hidden');
            passwordInput.focus();
            
            return false;
        }

        function closePasswordModal() {
            const passwordModal = document.getElementById('passwordModal');
            const passwordInput = document.getElementById('confirmPassword');
            const passwordError = document.getElementById('passwordError');
            
            passwordModal.classList.add('hidden');
            passwordInput.value = '';
            passwordError.classList.add('hidden');
            currentForm = null;
        }

        function confirmPasswordAndSubmit() {
            const passwordInput = document.getElementById('confirmPassword');
            const passwordError = document.getElementById('passwordError');
            const password = passwordInput.value.trim();
            
            if (!password) {
                passwordError.classList.remove('hidden');
                return;
            }

            // Add password to form data
            const passwordField = document.createElement('input');
            passwordField.type = 'hidden';
            passwordField.name = 'password_confirmation';
            passwordField.value = password;
            currentForm.appendChild(passwordField);

            // Submit the form
            currentForm.submit();
        }

        // Handle Enter key in password modal
        document.getElementById('confirmPassword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmPasswordAndSubmit();
            }
        });

        // Close modal when clicking outside
        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePasswordModal();
            }
        });
    </script>
</x-app-layout> 