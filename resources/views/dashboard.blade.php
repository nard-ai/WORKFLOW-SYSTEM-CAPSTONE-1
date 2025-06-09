<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- User Info Card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            
                            @if(isset($departmentName) && $departmentName !== 'N/A')
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Department: <span class="font-medium">{{ $departmentName }}</span></p>
                            @endif
                            @if(isset($position))
                                <p class="text-sm text-gray-600 dark:text-gray-400">Position: <span class="font-medium">{{ ucfirst($position) }}</span></p>
                            @endif
                        </div>
                        <a href="{{ route('request.create') }}" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Request
                        </a>
                    </div>
                </div>
            </div>

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
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $monthlyCount }}</p>
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
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $yearlyCount }}</p>
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
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $avgProcessingTime }}</p>
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
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $approvalRate }}%</p>
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
                            @php
                                $tabs = [
                                    'all' => 'All Requests',
                                    'pending' => 'Pending',
                                    'completed' => 'Completed',
                                    'rejected' => 'Rejected'
                                ];
                            @endphp

                            @foreach($tabs as $tab => $label)
                                <a href="{{ route('dashboard', ['tab' => $tab]) }}"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm
                                        @if($activeTab === $tab)
                                            border-blue-500 text-blue-600 dark:text-blue-400
                                        @else
                                            border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                                            dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-300
                                        @endif"
                                >
                                    {{ $label }}
                                    <span class="ml-2 py-0.5 px-2 text-xs rounded-full
                                        @if($activeTab === $tab)
                                            bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400
                                        @else
                                            bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400
                                        @endif"
                                    >
                                        {{ $counts[$tab] }}
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
                                @if($activeTab === 'all')
                                    Get started by creating a new request.
                                @else
                                    No {{ strtolower($tabs[$activeTab]) }} requests at the moment.
                                @endif
                            </p>
                            @if($activeTab === 'all')
                                <div class="mt-6">
                                    <a href="{{ route('request.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create New Request
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="overflow-x-auto mt-6">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title/Subject</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Academic Year</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        @if($activeTab === 'rejected')
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reason</th>
                                        @endif
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($requests as $request)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->form_id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $request->form_type === 'IOM' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                    {{ $request->form_type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ Str::limit($request->title, 30) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                @php
                                                    // Fixed academic year instead of dynamic calculation
                                                    $academicYear = '2024-2025';
                                                @endphp
                                                {{ $academicYear }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                @if($request->status === 'Approved')
                                                    {{ optional($request->approvals->where('action', 'Approved')->sortByDesc('action_date')->first())->action_date?->setTimezone(config('app.timezone'))->format('M j, Y g:i A') ?? 'N/A' }}
                                                @elseif($request->status === 'Rejected')
                                                    {{ optional($request->approvals->where('action', 'Rejected')->sortByDesc('action_date')->first())->action_date?->setTimezone(config('app.timezone'))->format('M j, Y g:i A') ?? 'N/A' }}
                                                @else
                                                    {{ $request->date_submitted?->setTimezone(config('app.timezone'))->format('M j, Y g:i A') ?? 'N/A' }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @switch(strtolower($request->status))
                                                        @case('pending')
                                                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                            @break
                                                        @case('in progress')
                                                            bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                            @break
                                                        @case('pending department head approval')
                                                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                            @break
                                                        @case('pending target department approval')
                                                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
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
                                            @if($activeTab === 'rejected')
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ Str::limit(optional(optional($request->approvals)->where('action', 'Rejected')->sortByDesc('action_date')->first())->comments ?? 'No reason provided', 50) }}
                                                </td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('request.track', $request->form_id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $requests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
