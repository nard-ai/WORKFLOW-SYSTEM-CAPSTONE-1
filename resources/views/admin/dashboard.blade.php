<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- This Month's Requests --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <svg class="h-8 w-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Month</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $monthlyCount ?? 0 }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Requests</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- This Year's Requests --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <svg class="h-8 w-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Year</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $yearlyCount ?? 0 }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Requests</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Average Processing Time --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                                <svg class="h-8 w-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Average Time</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $avgProcessingTime ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Processing Time</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Approval Rate --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                                <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approval Rate</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $approvalRate ?? 0 }}%</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Success Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Requests Table with Tabs --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Tab Navigation --}}
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            @foreach($tabs as $tabKey => $label)
                                <a href="{{ route('admin.dashboard', ['tab' => $tabKey]) }}"  {{-- Ensure route name is correct --}}
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm
                                        @if($activeTab === $tabKey)
                                            border-blue-500 text-blue-600 dark:text-blue-400
                                        @else
                                            border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                                            dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-300
                                        @endif"
                                >
                                    {{ $label }}
                                    <span class="ml-2 py-0.5 px-2 text-xs rounded-full
                                        @if($activeTab === $tabKey)
                                            bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400
                                        @else
                                            bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400
                                        @endif"
                                    >
                                        {{ $counts[$tabKey] ?? 0 }}
                                    </span>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    {{-- Table Content --}}
                    @if($requests->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No requests found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if($activeTab === 'all_requests')
                                    No requests have been made yet.
                                @else
                                    No {{ strtolower($tabs[$activeTab] ?? 'selected') }} requests at the moment.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto mt-6">
                            <table id="adminRequestsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requester</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th> {{-- Changed from Dept.(REQ) --}}
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title/Subject</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Academic Year</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Submitted</th>
                                        {{-- <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Last Update</th> --}} {{-- Removed --}}
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($requests as $request)
                                        <tr data-status="{{ strtolower($request->status) }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">{{ $request->form_id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                                @php
                                                    $fullName = 'N/A';
                                                    if ($request->requester && $request->requester->employeeInfo) {
                                                        $fName = $request->requester->employeeInfo->FirstName;
                                                        $lName = $request->requester->employeeInfo->LastName;
                                                        // array_filter removes null, false, empty strings, 0 which might be an issue if names can be '0'
                                                        // A more direct check for non-empty strings might be better if names can be '0'
                                                        $nameParts = [];
                                                        if (!empty($fName)) $nameParts[] = $fName;
                                                        if (!empty($lName)) $nameParts[] = $lName;

                                                        if (!empty($nameParts)) {
                                                            $fullName = implode(' ', $nameParts);
                                                        }
                                                    }
                                                @endphp
                                                {{ $fullName }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                                {{ optional(optional($request->requester)->department)->dept_name ?? 'N/A' }}
                                            </td>                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if($request->form_type === 'IOM')
                                                        bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300
                                                    @elseif($request->form_type === 'Leave') {{-- Corrected case for Leave --}}
                                                        bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                                    @else
                                                        bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                                    @endif
                                                ">
                                                    {{ $request->form_type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-left text-sm text-gray-900 dark:text-gray-100">
                                                @if($request->form_type === 'IOM')
                                                    {{ $request->title ?? 'N/A' }} {{-- Changed to use form_requests.title for IOM subject --}}
                                                @elseif($request->form_type === 'LEAVE')
                                                    {{ optional($request->leaveDetails)->leave_type ?? 'N/A' }}
                                                @else
                                                    {{ $request->title ?? 'N/A' }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">2024-2025</td> {{-- Static Academic Year --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                                {{ $request->date_submitted ? \Carbon\Carbon::parse($request->date_submitted)->format('M j, Y g:i A') : 'N/A' }}
                                            </td>
                                            {{-- Removed Last Update Cell --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @switch(strtolower($request->status))
                                                        @case('pending')
                                                        @case('pending department head approval')
                                                        @case('pending target department approval')
                                                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                            @break
                                                        @case('in progress')
                                                            bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                            @break
                                                        @case('approved')
                                                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                            @break
                                                        @case('rejected')
                                                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                            @break
                                                        @case('noted')
                                                            bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200
                                                            @break
                                                        @default
                                                            bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                                    @endswitch
                                                ">
                                                    {{ $request->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <a href="{{ route('admin.request.track', $request->form_id) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">View Details</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- Pagination Links --}}
                        @if ($requests instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="mt-4">
                                {{ $requests->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#adminRequestsTable').DataTable({
                responsive: true,
                lengthChange: false,
                autoWidth: false,
                // "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
                "buttons": [
                    { extend: 'copy', className: 'btn-sm' },
                    { extend: 'csv', className: 'btn-sm' },
                    { extend: 'excel', className: 'btn-sm' },
                    { extend: 'pdf', className: 'btn-sm' },
                    { extend: 'print', className: 'btn-sm' },
                    { extend: 'colvis', className: 'btn-sm' }
                ],
                columnDefs: [
                    // New column indices after removing 'Last Update'
                    // ID: 0, Requester: 1, Department: 2, Type: 3, Title/Subject: 4, Academic Year: 5, Submitted: 6, Status: 7, Actions: 8
                    { responsivePriority: 1, targets: 0 }, // ID
                    { responsivePriority: 2, targets: 3 }, // Type
                    { responsivePriority: 3, targets: 4 }, // Title/Subject
                    { responsivePriority: 4, targets: 7 }, // Status (new index)
                    { responsivePriority: 5, targets: 8 }, // Actions (new index)
                    { targets: [0, 1, 2, 3, 5, 6, 7, 8], className: 'text-center' }, // Adjusted for new indices
                    { targets: [4], className: 'text-left' }    // Title/Subject
                ],
                // "order": [[ 6, "desc" ]], // Default order by Submitted date (index 6 is still correct for Submitted)
                initComplete: function () {
                    table.buttons().container().appendTo('#requestsTable_wrapper .col-md-6:eq(0)');
                }
            });

            // Tab filtering
            $('.nav-tabs a').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
                var status = $(this).data('status-filter');
                if (status === 'all_requests') {
                    table.column(7).search('').draw(); // Status column is now index 7
                } else {
                    table.column(7).search('^' + status + '$', true, false).draw(); // Status column is now index 7
                }
            });

            // Activate tab based on URL parameter or default
            var activeTab = "{{ $activeTab }}";
            var statusToFilter = activeTab;
            if (activeTab === 'all_requests') {
                statusToFilter = ''; 
            } else if (activeTab === 'pending') {
                statusToFilter = '^pending$'; 
            } else {
                statusToFilter = '^' + activeTab + '$';
            }

            if (statusToFilter) {
                table.column(7).search(statusToFilter, true, false).draw(); // Status column is now index 7
            }
            $('.nav-tabs a[href="#' + activeTab + '"]').tab('show');
            $('.nav-tabs a[data-status-filter="' + activeTab + '"]').addClass('active');
        });

        function viewRequestDetails(formId) {
            alert('View details for form ID: ' + formId);
        }
    </script>
    @endpush

</x-app-layout>
