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

                                                        <div class="permissions-options {{ $employee->accessRole !== 'Approver' ? 'hidden' : '' }}">
                                                            <label class="inline-flex items-center">
                                                                <input type="hidden" name="can_approve_pending" value="0">
                                                                <input type="checkbox" 
                                                                    name="can_approve_pending" 
                                                                    value="1"
                                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                                    {{ $employee->approverPermissions?->can_approve_pending ? 'checked' : '' }}>
                                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Pending</span>
                                                            </label>

                                                            <label class="inline-flex items-center ml-4">
                                                                <input type="hidden" name="can_approve_in_progress" value="0">
                                                                <input type="checkbox" 
                                                                    name="can_approve_in_progress" 
                                                                    value="1"
                                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                                    {{ $employee->approverPermissions?->can_approve_in_progress ? 'checked' : '' }}>
                                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">In Progress</span>
                                                            </label>
                                                        </div>

                                                        <button type="submit" 
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

    <script>
        function togglePermissions(select) {
            const permissionsDiv = select.parentElement.querySelector('.permissions-options');
            if (select.value === 'Approver') {
                permissionsDiv.classList.remove('hidden');
            } else {
                permissionsDiv.classList.add('hidden');
                // Uncheck all checkboxes when switching to Viewer
                permissionsDiv.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
            }
        }
    </script>
</x-app-layout> 