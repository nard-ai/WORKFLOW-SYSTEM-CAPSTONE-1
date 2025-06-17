<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Approvals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Stats Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pending Requests</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['pending'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Today's Approvals</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['today'] ?? 0 }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Avg. Processing Time</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['avgTime'] ?? '0h' }}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Overdue Requests</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['overdue'] ?? 0 }}</p>
                        </div>
                    </div>

                    <!-- Enhanced Filters with Search -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <form action="{{ route('approvals.index') }}" method="GET" class="space-y-4">
                                <!-- Preserve active tab -->
                                <input type="hidden" name="tab" value="{{ $activeTab ?? 'awaiting' }}">
                                
                                <!-- Search Bar -->
                                <div class="w-full">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <input 
                                            type="text" 
                                            name="search" 
                                            id="searchInput"
                                            class="block w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Search by requester name, title, ID, or department..."
                                            value="{{ request('search') }}"
                                        >
                                        @if(request('search'))
                                            <button type="button" onclick="clearSearch()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Filter Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <div>
                                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Request Type</label>
                                        <select name="type" id="type" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Types</option>
                                            <option value="IOM" {{ request('type') === 'IOM' ? 'selected' : '' }}>IOM</option>
                                            <option value="Leave" {{ request('type') === 'Leave' ? 'selected' : '' }}>Leave</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date Range</label>
                                        <select name="date_range" id="date_range" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="all" {{ request('date_range') === 'all' ? 'selected' : '' }}>All Time</option>
                                            <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                                            <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                                            <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                                        <select name="priority" id="priority" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Priorities</option>
                                            <option value="Urgent" {{ request('priority') === 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                            <option value="Rush" {{ request('priority') === 'Rush' ? 'selected' : '' }}>Rush</option>
                                            <option value="Routine" {{ request('priority') === 'Routine' ? 'selected' : '' }}>Routine</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Per Page</label>
                                        <select name="per_page" id="per_page" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                    <div class="flex items-end space-x-2">
                                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            Apply
                                        </button>
                                        <button type="button" onclick="clearAllFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                            Clear
                                        </button>
                                    </div>
                                </div>

                                <!-- Active Filters Display -->
                                @if(request()->hasAny(['search', 'type', 'date_range', 'priority']) && (request('search') || request('type') || (request('date_range') && request('date_range') !== 'all') || request('priority')))
                                <div class="flex items-center space-x-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Active filters:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @if(request('search'))
                                            <span class="filter-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Search: "{{ Str::limit(request('search'), 20) }}"
                                                <button type="button" class="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-600 focus:outline-none transition-all duration-200" onclick="removeFilter('search')">
                                                    <span class="sr-only">Remove search filter</span>
                                                    <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                        <path stroke-linecap="round" stroke-width="1.5" d="m1 1 6 6m0-6-6 6" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endif
                                        @if(request('type'))
                                            <span class="filter-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                Type: {{ request('type') }}
                                                <button type="button" class="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-purple-400 hover:bg-purple-200 hover:text-purple-600 focus:outline-none transition-all duration-200" onclick="removeFilter('type')">
                                                    <span class="sr-only">Remove type filter</span>
                                                    <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                        <path stroke-linecap="round" stroke-width="1.5" d="m1 1 6 6m0-6-6 6" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endif
                                        @if(request('date_range') && request('date_range') !== 'all')
                                            <span class="filter-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Date: {{ ucfirst(str_replace('_', ' ', request('date_range'))) }}
                                                <button type="button" class="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-600 focus:outline-none transition-all duration-200" onclick="removeFilter('date_range')">
                                                    <span class="sr-only">Remove date filter</span>
                                                    <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                        <path stroke-linecap="round" stroke-width="1.5" d="m1 1 6 6m0-6-6 6" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endif
                                        @if(request('priority'))
                                            <span class="filter-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                Priority: {{ request('priority') }}
                                                <button type="button" class="ml-1.5 inline-flex items-center justify-center h-4 w-4 rounded-full text-orange-400 hover:bg-orange-200 hover:text-orange-600 focus:outline-none transition-all duration-200" onclick="removeFilter('priority')">
                                                    <span class="sr-only">Remove priority filter</span>
                                                    <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                                        <path stroke-linecap="round" stroke-width="1.5" d="m1 1 6 6m0-6-6 6" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Filter JavaScript Functions - Load immediately -->
                    <script>
                        // Make functions globally available immediately
                        window.clearSearch = function() {
                            console.log('clearSearch called');
                            const searchInput = document.getElementById('searchInput');
                            if (searchInput) {
                                searchInput.value = '';
                                
                                // Show loading feedback
                                const clearButton = document.querySelector('button[onclick="clearSearch()"]');
                                if (clearButton) {
                                    clearButton.classList.add('filter-button-loading');
                                    clearButton.disabled = true;
                                }
                                
                                const form = searchInput.closest('form');
                                if (form) {
                                    console.log('Submitting form to clear search');
                                    form.submit();
                                } else {
                                    console.error('Form not found for search input');
                                }
                            } else {
                                console.error('Search input not found');
                            }
                        };

                        // Clear all filters function
                        window.clearAllFilters = function() {
                            console.log('clearAllFilters called');
                            const form = document.querySelector('form');
                            if (form) {
                                const activeTab = form.querySelector('input[name="tab"]');
                                const tabValue = activeTab ? activeTab.value : 'awaiting';
                                
                                // Show loading feedback
                                const clearButton = document.querySelector('button[onclick="clearAllFilters()"]');
                                if (clearButton) {
                                    clearButton.classList.add('filter-button-loading');
                                    clearButton.disabled = true;
                                    clearButton.textContent = 'Clearing...';
                                }
                                
                                console.log('Redirecting to clear all filters, keeping tab:', tabValue);
                                // Keep only the tab parameter
                                window.location.href = window.location.pathname + '?tab=' + tabValue;
                            } else {
                                console.error('Form not found for clearAllFilters');
                            }
                        };

                        // Remove individual filter function
                        window.removeFilter = function(filterName) {
                            console.log('removeFilter called for:', filterName);
                            const url = new URL(window.location.href);
                            const params = new URLSearchParams(url.search);
                            
                            // Show loading feedback for the specific filter badge
                            const filterBadge = document.querySelector(`button[onclick="removeFilter('${filterName}')"]`);
                            if (filterBadge) {
                                const badge = filterBadge.closest('.filter-badge');
                                if (badge) {
                                    badge.style.opacity = '0.6';
                                    badge.style.pointerEvents = 'none';
                                }
                            }
                            
                            console.log('Before removing filter:', params.toString());
                            params.delete(filterName);
                            params.delete('page'); // Reset pagination when removing filters
                            
                            // If no parameters remain except tab, ensure tab is preserved
                            if (!params.has('tab')) {
                                params.set('tab', 'awaiting');
                            }
                            
                            console.log('After removing filter:', params.toString());
                            url.search = params.toString();
                            console.log('Redirecting to:', url.toString());
                            window.location.href = url.toString();
                        };

                        // Debug: Check if functions are available
                        console.log('Filter functions loaded:', {
                            clearSearch: typeof window.clearSearch,
                            clearAllFilters: typeof window.clearAllFilters,
                            removeFilter: typeof window.removeFilter
                        });
                    </script>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{-- Tab Navigation --}}
                            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                    @php
                                        $tabs = [
                                            'awaiting' => 'Awaiting Action',
                                            'approved' => 'Approved',
                                            'rejected' => 'Rejected',
                                            'noted' => 'Noted'
                                        ];
                                    @endphp

                                    @foreach($tabs as $tab => $label)
                                        <a href="{{ route('approvals.index', array_merge(request()->query(), ['tab' => $tab])) }}"
                                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm
                                                @if(($activeTab ?? 'awaiting') === $tab)
                                                    border-indigo-500 text-indigo-600 dark:text-indigo-400
                                                @else
                                                    border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300
                                                    dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-300
                                                @endif"
                                        >
                                            {{ $label }}
                                            @if(isset($tabCounts[$tab]))
                                                <span class="ml-2 py-0.5 px-2 text-xs rounded-full
                                                    @if(($activeTab ?? 'awaiting') === $tab)
                                                        bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-400
                                                    @else
                                                        bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400
                                                    @endif"
                                                >
                                                    {{ $tabCounts[$tab] }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </nav>
                            </div>

                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    @php
                                        $currentTab = $activeTab ?? 'awaiting';
                                        $tabTitles = [
                                            'awaiting' => 'Requests Awaiting Action',
                                            'approved' => 'Approved Requests',
                                            'rejected' => 'Rejected Requests',
                                            'noted' => 'Noted Requests'
                                        ];
                                    @endphp
                                    {{ $tabTitles[$currentTab] ?? 'Requests' }}
                                </h3>
                                @if(!$formRequests->isEmpty() && Auth::user()->accessRole === 'Approver' && ($activeTab ?? 'awaiting') === 'awaiting')
                                    <div class="flex space-x-2">
                                        <button id="batchApproveBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                                            Batch Approve
                                        </button>
                                        <button id="batchRejectBtn" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm">
                                            Batch Reject
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if($formRequests->isEmpty())
                                <div class="text-center py-8">
                                    @php
                                        $currentTab = $activeTab ?? 'awaiting';
                                        $emptyMessages = [
                                            'awaiting' => 'No requests currently need your action.',
                                            'approved' => 'You have not approved any requests yet.',
                                            'rejected' => 'You have not rejected any requests yet.',
                                            'noted' => 'You have not noted any requests yet.'
                                        ];
                                    @endphp
                                    <p class="text-gray-500 dark:text-gray-400">{{ $emptyMessages[$currentTab] ?? 'No requests found.' }}</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead>
                                            <tr>
                                                @if(($activeTab ?? 'awaiting') === 'awaiting' && Auth::user()->accessRole === 'Approver')
                                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                                                    </th>
                                                @endif
                                                
                                                {{-- Sortable Headers --}}
                                                @php
                                                    $currentSort = request('sort', 'date_submitted');
                                                    $currentDirection = request('direction', 'desc');
                                                    
                                                    function getSortUrl($field, $currentSort, $currentDirection) {
                                                        $direction = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
                                                        return request()->url() . '?' . http_build_query(array_merge(request()->query(), [
                                                            'sort' => $field,
                                                            'direction' => $direction
                                                        ]));
                                                    }
                                                    
                                                    function getSortIcon($field, $currentSort, $currentDirection) {
                                                        if ($currentSort !== $field) {
                                                            return '<svg class="w-3 h-3 ml-1 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>';
                                                        }
                                                        
                                                        if ($currentDirection === 'asc') {
                                                            return '<svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>';
                                                        } else {
                                                            return '<svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"></path></svg>';
                                                        }
                                                    }
                                                @endphp
                                                
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    <a href="{{ getSortUrl('form_id', $currentSort, $currentDirection) }}" class="group flex items-center hover:text-gray-900 dark:hover:text-gray-100">
                                                        ID
                                                        {!! getSortIcon('form_id', $currentSort, $currentDirection) !!}
                                                    </a>
                                                </th>
                                                
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    <a href="{{ getSortUrl('form_type', $currentSort, $currentDirection) }}" class="group flex items-center hover:text-gray-900 dark:hover:text-gray-100">
                                                        Type
                                                        {!! getSortIcon('form_type', $currentSort, $currentDirection) !!}
                                                    </a>
                                                </th>
                                                
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requester</th>
                                                
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    <a href="{{ getSortUrl('title', $currentSort, $currentDirection) }}" class="group flex items-center hover:text-gray-900 dark:hover:text-gray-100">
                                                        Title/Subject
                                                        {!! getSortIcon('title', $currentSort, $currentDirection) !!}
                                                    </a>
                                                </th>
                                                
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    <a href="{{ getSortUrl('date_submitted', $currentSort, $currentDirection) }}" class="group flex items-center hover:text-gray-900 dark:hover:text-gray-100">
                                                        @if(($activeTab ?? 'awaiting') === 'awaiting')
                                                            Submitted
                                                        @else
                                                            Action Date
                                                        @endif
                                                        {!! getSortIcon('date_submitted', $currentSort, $currentDirection) !!}
                                                    </a>
                                                </th>
                                                
                                                @if(($activeTab ?? 'awaiting') === 'awaiting')
                                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Wait Time</th>
                                                @endif
                                                
                                                @if(request('type') === 'IOM')
                                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        <a href="{{ getSortUrl('priority', $currentSort, $currentDirection) }}" class="group flex items-center hover:text-gray-900 dark:hover:text-gray-100">
                                                            Priority
                                                            {!! getSortIcon('priority', $currentSort, $currentDirection) !!}
                                                        </a>
                                                    </th>
                                                @endif
                                                
                                                @if(in_array($activeTab ?? 'awaiting', ['rejected', 'noted']))
                                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Comments</th>
                                                @endif
                                                
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    <a href="{{ getSortUrl('status', $currentSort, $currentDirection) }}" class="group flex items-center hover:text-gray-900 dark:hover:text-gray-100">
                                                        Status
                                                        {!! getSortIcon('status', $currentSort, $currentDirection) !!}
                                                    </a>
                                                </th>
                                                
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                            @foreach($formRequests as $request)
                                                @php
                                                    $currentTab = $activeTab ?? 'awaiting';
                                                    $userApproval = null;
                                                    if ($currentTab !== 'awaiting') {
                                                        $actionMap = [
                                                            'approved' => 'Approved',
                                                            'rejected' => 'Rejected', 
                                                            'noted' => 'Noted'
                                                        ];
                                                        $userApproval = $request->approvals->where('approver_id', Auth::user()->accnt_id)
                                                                                          ->where('action', $actionMap[$currentTab])
                                                                                          ->first();
                                                    }
                                                    
                                                    // Check if request is overdue (more than 2 days old)
                                                    $isOverdue = $request->date_submitted && 
                                                                $request->date_submitted->lt(now()->subDays(2)) && 
                                                                in_array(strtolower($request->status), ['pending', 'in progress']);
                                                                
                                                    // Priority indicator for IOM requests
                                                    $priority = $request->form_type === 'IOM' && $request->iomDetails ? $request->iomDetails->priority : null;
                                                @endphp
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150 {{ $isOverdue ? 'border-l-4 border-red-500' : '' }}">
                                                    @if(($activeTab ?? 'awaiting') === 'awaiting' && Auth::user()->accessRole === 'Approver')
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <input type="checkbox" name="selected_requests[]" value="{{ $request->form_id }}" class="request-checkbox rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                                                        </td>
                                                    @endif
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        <div class="flex items-center">
                                                            {{ $request->form_id }}
                                                            @if($isOverdue)
                                                                <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200" title="Overdue">
                                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            {{ $request->form_type === 'IOM' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                            {{ $request->form_type }}
                                                        </span>
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-8 w-8">
                                                                <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                                                        {{ strtoupper(substr($request->requester->employeeInfo->FirstName ?? 'U', 0, 1)) }}{{ strtoupper(substr($request->requester->employeeInfo->LastName ?? 'U', 0, 1)) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="ml-3">
                                                                <div class="font-medium">{{ $request->requester->employeeInfo->FirstName ?? 'N/A' }} {{ $request->requester->employeeInfo->LastName ?? '' }}</div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ $request->requester->department->dept_name ?? 'N/A' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                        <div class="max-w-xs">
                                                            <div class="font-medium">{{ Str::limit($request->title, 40) }}</div>
                                                            @if($request->form_type === 'IOM' && $request->iomDetails && $request->iomDetails->purpose)
                                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($request->iomDetails->purpose, 60) }}</div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        @if(($activeTab ?? 'awaiting') === 'awaiting')
                                                            <div>{{ $request->date_submitted?->format('M j, Y') ?? 'N/A' }}</div>
                                                            <div class="text-xs">{{ $request->date_submitted?->format('g:i A') ?? '' }}</div>
                                                        @else
                                                            <div>{{ $userApproval?->action_date?->format('M j, Y') ?? 'N/A' }}</div>
                                                            <div class="text-xs">{{ $userApproval?->action_date?->format('g:i A') ?? '' }}</div>
                                                        @endif
                                                    </td>
                                                    
                                                    @if(($activeTab ?? 'awaiting') === 'awaiting')
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                            @if($request->date_submitted)
                                                                @php
                                                                    $waitTime = $request->date_submitted->diffInHours(now());
                                                                @endphp
                                                                <span class="
                                                                    @if($waitTime < 24) text-green-600 dark:text-green-400
                                                                    @elseif($waitTime < 48) text-yellow-600 dark:text-yellow-400
                                                                    @else text-red-600 dark:text-red-400
                                                                    @endif
                                                                ">
                                                                    {{ $request->date_submitted->diffForHumans() }}
                                                                </span>
                                                            @else
                                                                <span class="text-gray-400">N/A</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    
                                                    @if(request('type') === 'IOM')
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            @if($priority)
                                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                                    @switch(strtolower($priority))
                                                                        @case('urgent')
                                                                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                                            @break
                                                                        @case('rush')
                                                                            bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                                            @break
                                                                        @case('routine')
                                                                            bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                                            @break
                                                                        @default
                                                                            bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                                                    @endswitch">
                                                                    {{ $priority }}
                                                                </span>
                                                            @else
                                                                <span class="text-gray-400 text-xs">N/A</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    
                                                    @if(in_array($activeTab ?? 'awaiting', ['rejected', 'noted']))
                                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                            <div class="max-w-xs">
                                                                {{ Str::limit($userApproval?->comments ?? 'No comments', 50) }}
                                                            </div>
                                                        </td>
                                                    @endif
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
                                                            @endswitch"
                                                            data-status="{{ $request->status }}">
                                                            {{ $request->status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('approvals.show', $request) }}" 
                                                           class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                            @if(($activeTab ?? 'awaiting') === 'awaiting')
                                                                View/Action
                                                            @else
                                                                View Details
                                                            @endif
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">
                                    {{ $formRequests->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Action Modal -->
    <div id="batchActionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"></h3>
                <input type="hidden" id="batchAction" value="">
                
                <!-- Name Input -->
                <div class="mb-4">
                    <label for="fullName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Full Name
                    </label>
                    <input type="text" 
                        id="fullName" 
                        name="fullName" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 bg-gray-50 dark:bg-gray-700"
                        style="text-transform: uppercase;"
                        value="{{ Auth::user()->employeeInfo->FirstName }} {{ Auth::user()->employeeInfo->LastName }}"
                        readonly
                        required>
                </div>

                <!-- Signature Style Selection -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Choose your signature style
                    </label>
                    <div class="mt-2 p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                        <div id="signatureStyles" class="grid grid-cols-2 gap-4">
                            {{-- Signature styles will be loaded here --}}
                        </div>
                        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                            Select a style and your name will be converted to a signature
                        </div>
                        <span id="signatureError" class="hidden text-sm text-red-500 block text-center mt-2">
                            Please select a signature style
                        </span>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="mt-4">
                    <div class="flex justify-between items-center">
                        <label for="batchComment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Comments <span id="commentRequired" class="text-red-500 hidden">*</span>
                        </label>
                        <span id="commentError" class="text-sm text-red-500 hidden">Comments are required for rejection</span>
                    </div>
                    <textarea id="batchComment" name="comment" rows="3" 
                             class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeBatchModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancel
                    </button>
                    <button type="button" onclick="submitBatchAction()"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Mr+Dafoe&family=Homemade+Apple&family=Pacifico&family=Dancing+Script&display=swap');
    
    .signature-style {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        cursor: pointer;
        padding: 1rem;
    }

    .signature-style.selected {
        border-color: #2563eb;
        background-color: #eff6ff;
        box-shadow: 0 0 0 2px #3b82f6;
    }

    .signature-style:hover:not(.selected) {
        border-color: #93c5fd;
        background-color: #f8fafc;
    }

    .preview-text {
        font-size: 1.75rem;
        line-height: 1.2;
        text-align: center;
        width: 100%;
        color: #1f2937;
        margin-bottom: 0.5rem;
        word-break: break-word;
    }

    .style-name {
        font-size: 0.75rem;
        color: #6b7280;
        text-align: center;
        width: 100%;
    }

    .signature-style::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(to right, #2563eb, #3b82f6);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .signature-style.selected::after {
        opacity: 1;
    }
</style>

<script>
let currentAction = '';
let selectedSignatureId = null;
let selectedFontFamily = null;

// Function to convert text to base64 image
function textToImage(text, fontFamily) {
    if (!text || !fontFamily) {
        console.error('Missing required parameters for textToImage:', { text, fontFamily });
        throw new Error('Missing text or font family for signature generation');
    }
    
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        if (!ctx) {
            console.error('Failed to get canvas context');
            throw new Error('Browser does not support canvas');
        }
        
        // Set canvas size
        canvas.width = 600;
        canvas.height = 150;
        
        // Configure text style
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#1f2937';
        
        // Calculate font size based on text length
        const maxFontSize = 72;
        const minFontSize = 48;
        const calculatedSize = Math.max(minFontSize, Math.min(maxFontSize, 800 / text.length));
        
        // Ensure text is uppercase before drawing
        const uppercaseText = text.toUpperCase();
        
        // Use a fallback font if the specified font fails
        const fallbackFont = "cursive, serif";
        ctx.font = `${calculatedSize}px ${fontFamily}, ${fallbackFont}`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        // Add subtle shadow
        ctx.shadowColor = 'rgba(0, 0, 0, 0.1)';
        ctx.shadowBlur = 2;
        ctx.shadowOffsetY = 2;
        
        // Draw text
        ctx.fillText(uppercaseText, canvas.width / 2, canvas.height / 2);
        
        // Try to generate the data URL
        try {
            return canvas.toDataURL('image/png');
        } catch (e) {
            console.error('Error generating canvas data URL:', e);
            throw new Error('Failed to generate signature image');
        }
    } catch (error) {
        console.error('Error in textToImage function:', error);
        throw error;
    }
}

function showToast(type, message) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.innerHTML = `
        <x-toast-notification type="${type}" message="${message}" />
    `.trim();
    document.body.appendChild(toast.firstChild);
}

window.submitBatchAction = function() {
    console.log('Starting batch action submission...');
    
    const selectedRequests = Array.from(document.querySelectorAll('.request-checkbox:checked')).map(cb => cb.value);
    const action = document.getElementById('batchAction').value;
    const comment = document.getElementById('batchComment').value;
    const fullName = document.getElementById('fullName').value.toUpperCase();
    
    console.log('Batch action parameters:', {
        action: action,
        selectedRequests: selectedRequests.length,
        hasComment: !!comment.trim(),
        fullName: fullName,
        selectedSignatureId: selectedSignatureId,
        selectedFontFamily: selectedFontFamily
    });
    
    // Validate selected requests
    if (selectedRequests.length === 0) {
        alert('Please select at least one request to process.');
        return;
    }
    
    // Get selected signature style - make sure the selection is still valid
    const selectedStyle = document.querySelector('.signature-style.selected');
    if (!selectedStyle || !selectedSignatureId || !selectedFontFamily) {
        console.error('Missing signature style information:', {
            selectedStyle: !!selectedStyle,
            selectedSignatureId: selectedSignatureId,
            selectedFontFamily: selectedFontFamily
        });
        document.getElementById('signatureError').classList.remove('hidden');
        return;
    }
    
    // Validate comment for rejection
    if (action === 'reject' && !comment.trim()) {
        document.getElementById('commentError').classList.remove('hidden');
        document.getElementById('batchComment').classList.add('border-red-500');
        return;
    }

    try {
        // Generate signature image
        const signatureImage = textToImage(fullName, selectedFontFamily);
        
        // Create form data
        const formData = {
            selected_requests: selectedRequests,
            action: action,
            comment: comment,
            signature_style_id: selectedSignatureId,
            signature: signatureImage
        };
        
        console.log('Submitting batch action with valid data');
        
        // Send request
        fetch('{{ route("approvals.batch") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Batch action response:', data);
            if (data.success) {
                showToast('success', data.message);
                closeBatchModal();
                // Reload the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showToast('error', data.message || 'An error occurred while processing the batch action');
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join('\n');
                    console.error('Validation errors:', errorMessages);
                    alert('Validation errors:\n' + errorMessages);
                }
            }
        })
        .catch(error => {
            console.error('Error submitting batch action:', error);
            showToast('error', 'An error occurred while processing the request.');
            alert('Error: ' + error.message);
        });
        
    } catch (error) {
        console.error('Error generating signature:', error);
        alert('Error creating signature. Please try selecting a different signature style.');
        return;
    }

    // The fetch request is now handled directly inside the try/catch block above
}

window.closeBatchModal = function() {
    document.getElementById('batchActionModal').classList.add('hidden');
    document.getElementById('batchComment').value = '';
    document.getElementById('commentError').classList.add('hidden');
    document.getElementById('signatureError').classList.add('hidden');
    selectedSignatureId = null;
    selectedFontFamily = null;
}

document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const requestCheckboxes = document.querySelectorAll('.request-checkbox');
    const batchApproveBtn = document.getElementById('batchApproveBtn');
    const batchRejectBtn = document.getElementById('batchRejectBtn');
    const batchActionModal = document.getElementById('batchActionModal');
    const commentRequired = document.getElementById('commentRequired');
    const commentError = document.getElementById('commentError');
    const batchComment = document.getElementById('batchComment');
    const signatureError = document.getElementById('signatureError');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            requestCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            updateBatchButtonsState();
        });
    }

    requestCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBatchButtonsState();
            updateSelectAllState();
        });
    });
    
    // Add event listener to fullName input to update previews
    const fullNameInput = document.getElementById('fullName');
    if (fullNameInput) {
        fullNameInput.addEventListener('input', function() {
            // Automatically make uppercase
            const currentValue = this.value;
            const upperValue = currentValue.toUpperCase();
            
            // Only update if different to avoid cursor jumping
            if (currentValue !== upperValue) {
                this.value = upperValue;
            }
            
            updateAllPreviews(upperValue);
        });
    }

    function updateSelectAllState() {
        if (!selectAll) return;
        const allChecked = Array.from(requestCheckboxes).every(checkbox => checkbox.checked);
        const someChecked = Array.from(requestCheckboxes).some(checkbox => checkbox.checked);
        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
    }

    function updateBatchButtonsState() {
        const hasSelection = Array.from(requestCheckboxes).some(checkbox => checkbox.checked);
        
        if (batchApproveBtn) {
            batchApproveBtn.disabled = !hasSelection;
            batchApproveBtn.classList.toggle('opacity-50', !hasSelection);
        }
        
        if (batchRejectBtn) {
            batchRejectBtn.disabled = !hasSelection;
            batchRejectBtn.classList.toggle('opacity-50', !hasSelection);
        }
    }

    if (batchApproveBtn) {
        batchApproveBtn.addEventListener('click', () => openBatchModal('approve'));
    }

    if (batchRejectBtn) {
        batchRejectBtn.addEventListener('click', () => openBatchModal('reject'));
    }

    window.openBatchModal = function(action) {
        // Validate permissions first
        const validationResult = validateRequestSelection();
        if (!validationResult.valid) {
            alert(validationResult.message);
            return;
        }

        currentAction = action;
        document.getElementById('batchAction').value = action;
        const selectedCount = document.querySelectorAll('.request-checkbox:checked').length;
        const modalTitle = document.getElementById('modalTitle');
        
        const actionText = {
            'approve': 'approve',
            'reject': 'reject'
        }[action];
        
        modalTitle.textContent = `Are you sure you want to ${actionText} ${selectedCount} selected request${selectedCount > 1 ? 's' : ''}?`;
        
        // Reset form state
        commentRequired.classList.toggle('hidden', action !== 'reject');
        batchComment.value = '';
        commentError.classList.add('hidden');
        signatureError.classList.add('hidden');
        selectedSignatureId = null;
        selectedFontFamily = null;
        
        // Load signature styles
        loadSignatureStyles();
        
        batchActionModal.classList.remove('hidden');
    }

    // Load signature styles
    function loadSignatureStyles() {
        const container = document.getElementById('signatureStyles');
        container.innerHTML = '<p class="text-center w-full col-span-2 py-2">Loading signature styles...</p>';
        
        fetch('{{ route("signature-styles.index") }}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load signature styles');
                }
                return response.json();
            })
            .then(data => {
                console.log('Signature styles response:', data);
                container.innerHTML = '';
                
                // Check for data.styles (from the updated controller)
                const styles = data.styles || data;
                
                if (!styles || styles.length === 0) {
                    container.innerHTML = '<p class="text-center w-full col-span-2 py-2">No signature styles available</p>';
                    return;
                }
                
                styles.forEach(style => {
                    const div = document.createElement('div');
                    div.className = 'signature-style';
                    
                    const signatureText = document.createElement('div');
                    signatureText.className = 'preview-text';
                    signatureText.style.fontFamily = style.font_family;
                    signatureText.textContent = 'Your Signature';
                    
                    const styleName = document.createElement('div');
                    styleName.className = 'style-name';
                    styleName.textContent = style.name;
                    
                    div.appendChild(signatureText);
                    div.appendChild(styleName);
                    div.onclick = () => selectStyle(style.id, style.font_family, div);
                    container.appendChild(div);
                });

                // Update previews with current name if exists
                const currentName = document.getElementById('fullName').value;
                if (currentName) {
                    updateAllPreviews(currentName);
                }
            })
            .catch(error => {
                console.error('Error loading signature styles:', error);
                container.innerHTML = `<p class="text-center w-full col-span-2 py-2 text-red-500">Error loading signature styles: ${error.message}</p>`;
            });
    }

    // Function to update all signature previews
    function updateAllPreviews(name) {
        if (name === undefined || name === null) {
            console.warn('updateAllPreviews called with null/undefined name');
            name = '';
        }
        
        try {
            const displayText = name.trim() ? name.toUpperCase() : 'Your Signature';
            console.log('Updating preview text to:', displayText);
            
            const previewElements = document.querySelectorAll('.preview-text');
            console.log(`Found ${previewElements.length} preview elements to update`);
            
            previewElements.forEach(preview => {
                preview.textContent = displayText;
            });
        } catch (error) {
            console.error('Error updating preview text:', error);
        }
    }

    function selectStyle(styleId, fontFamily, element) {
        // Validate inputs
        if (!styleId || !fontFamily || !element) {
            console.error('Invalid style selection parameters:', { styleId, fontFamily, element });
            return;
        }
        
        console.log('Selecting style:', { styleId, fontFamily });
        
        // Update selection state
        document.querySelectorAll('.signature-style').forEach(div => {
            div.classList.remove('selected');
        });
        
        // Add selected class to the element
        element.classList.add('selected');
        
        // Store the selected style information
        selectedSignatureId = styleId;
        selectedFontFamily = fontFamily;
        
        // Hide any previous errors
        document.getElementById('signatureError').classList.add('hidden');
        
        // Log successful selection
        console.log('Style selected:', { 
            selectedId: selectedSignatureId, 
            selectedFont: selectedFontFamily 
        });
    }

    function validateRequestSelection() {
        const selectedRequests = document.querySelectorAll('.request-checkbox:checked');
        const selectedStatuses = Array.from(selectedRequests).map(checkbox => {
            const row = checkbox.closest('tr');
            return row.querySelector('[data-status]').getAttribute('data-status');
        });

        // Get user's permissions from data attributes
        const canApprovePending = {{ Auth::user()->approverPermissions?->can_approve_pending ? 'true' : 'false' }};
        const canApproveInProgress = {{ Auth::user()->approverPermissions?->can_approve_in_progress ? 'true' : 'false' }};
        const isHead = {{ Auth::user()->position === 'Head' ? 'true' : 'false' }};
        const isVPAA = {{ Auth::user()->position === 'VPAA' ? 'true' : 'false' }};

        // Both VPAA and Department Heads can approve all requests
        if (isHead || isVPAA) {
            return { valid: true, message: '' };
        }

        // Check if user has permission for all selected requests
        const invalidRequests = selectedStatuses.filter(status => {
            if (status === 'Pending' && !canApprovePending) return true;
            if ((status === 'In Progress' || status === 'Pending Target Department Approval') && !canApproveInProgress) return true;
            return false;
        });

        if (invalidRequests.length > 0) {
            return {
                valid: false,
                message: `You don't have permission to act on ${invalidRequests.length} selected request(s). Please check your approver permissions.`
            };
        }

        return { valid: true, message: '' };
    }
});
</script>

@push('styles')
<style>
    /* Enhanced filter UI styles */
    .filter-loading {
        border-radius: 0.5rem;
    }
    
    .filter-button-loading {
        pointer-events: none;
        opacity: 0.6;
    }
    
    .filter-button-loading::after {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        margin: auto;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Enhanced filter badge styling */
    .filter-badge {
        transition: all 0.2s ease-in-out;
    }
    
    .filter-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .filter-badge button:hover {
        transform: scale(1.1);
    }
    
    /* Loading state for forms */
    .form-loading {
        position: relative;
        overflow: hidden;
    }
    
    .form-loading::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        animation: loading-shimmer 1.5s infinite;
        z-index: 1;
    }
    
    @keyframes loading-shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    /* Smooth transitions for filter elements */
    select, input[type="text"] {
        transition: all 0.2s ease-in-out;
    }
    
    select:focus, input[type="text"]:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
</style>
@endpush

@push('scripts')
<script>
    // Enhanced search functionality with debounce
    document.addEventListener('DOMContentLoaded', function() {
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Auto-submit form after 500ms of no typing
                    if (e.target.value.length > 2 || e.target.value.length === 0) {
                        // Add subtle loading indicator
                        const searchIcon = e.target.parentElement.querySelector('svg');
                        if (searchIcon) {
                            searchIcon.classList.add('animate-pulse');
                        }
                        
                        e.target.closest('form').submit();
                    }
                }, 500);
            });
            
            // Add immediate feedback for typing
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.closest('form').submit();
                }
            });
        }
    });

    // Auto-apply filters when changed (except search)
    document.addEventListener('DOMContentLoaded', function() {
        ['type', 'date_range', 'priority', 'per_page'].forEach(filterName => {
            const element = document.getElementById(filterName);
            if (element) {
                element.addEventListener('change', function() {
                    // Show loading state
                    const form = this.closest('form');
                    if (form) {
                        // Add loading class to form
                        form.classList.add('opacity-75', 'pointer-events-none');
                        
                        // Submit form
                        form.submit();
                    }
                });
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
            
            // Escape to clear search if focused
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('searchInput');
                if (searchInput && document.activeElement === searchInput) {
                    window.clearSearch();
                }
                
                // Close any open modals
                const modals = document.querySelectorAll('.modal, [class*="modal"]');
                modals.forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                    }
                });
            }
        });
    });

    // Enhanced batch selection with better UX
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const requestCheckboxes = document.querySelectorAll('.request-checkbox');
        const batchButtons = document.querySelectorAll('#batchApproveBtn, #batchRejectBtn');

        // Add loading state for filter actions
        function showFilterLoading() {
            const filterContainer = document.querySelector('.bg-white.dark\\:bg-gray-800.overflow-hidden.shadow-sm.sm\\:rounded-lg.mb-6');
            if (filterContainer && !filterContainer.querySelector('.filter-loading')) {
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'filter-loading absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 flex items-center justify-center z-10';
                loadingDiv.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Applying filters...</span>
                    </div>
                `;
                
                if (filterContainer.style.position !== 'relative') {
                    filterContainer.style.position = 'relative';
                }
                filterContainer.appendChild(loadingDiv);
                
                // Remove after 10 seconds as fallback
                setTimeout(() => {
                    if (loadingDiv.parentNode) {
                        loadingDiv.remove();
                    }
                }, 10000);
            }
        }

        // Add event listeners to all filter-related buttons
        const filterButtons = document.querySelectorAll('button[onclick*="clear"], button[onclick*="remove"]');
        filterButtons.forEach(button => {
            button.addEventListener('click', showFilterLoading);
        });

        // Add event listeners to form submissions
        const filterForm = document.querySelector('form');
        if (filterForm) {
            filterForm.addEventListener('submit', showFilterLoading);
        }

        // Select all functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                requestCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBatchButtons();
            });
        }

        // Individual checkbox change
        requestCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBatchButtons();
                updateSelectAllState();
            });
        });

        function updateSelectAllState() {
            if (!selectAllCheckbox) return;
            
            const checkedCount = document.querySelectorAll('.request-checkbox:checked').length;
            const totalCount = requestCheckboxes.length;
            
            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === totalCount) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        function updateBatchButtons() {
            const checkedCount = document.querySelectorAll('.request-checkbox:checked').length;
            
            batchButtons.forEach(button => {
                if (checkedCount > 0) {
                    button.disabled = false;
                    button.classList.remove('opacity-50', 'cursor-not-allowed');
                    
                    // Update button text to show count
                    const action = button.id.includes('Approve') ? 'Approve' : 'Reject';
                    button.textContent = `${action} Selected (${checkedCount})`;
                } else {
                    button.disabled = true;
                    button.classList.add('opacity-50', 'cursor-not-allowed');
                    
                    // Reset button text
                    const action = button.id.includes('Approve') ? 'Batch Approve' : 'Batch Reject';
                    button.textContent = action;
                }
            });
        }

        // Initialize button states
        updateBatchButtons();
    });

    // Loading state for table actions
    function showTableLoading() {
        const tableContainer = document.querySelector('.overflow-x-auto');
        if (tableContainer) {
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 flex items-center justify-center z-10';
            loadingOverlay.innerHTML = `
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Loading...</span>
                </div>
            `;
            
            tableContainer.style.position = 'relative';
            tableContainer.appendChild(loadingOverlay);
            
            // Remove after 5 seconds as fallback
            setTimeout(() => {
                if (loadingOverlay.parentNode) {
                    loadingOverlay.remove();
                }
            }, 5000);
        }
    }

    // Show loading on form submit and pagination clicks
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', showTableLoading);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + F to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Escape to clear search
        if (e.key === 'Escape') {
            const searchInput = document.getElementById('searchInput');
            if (searchInput && document.activeElement === searchInput) {
                clearSearch();
            }
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const priorityDiv = document.getElementById('priority').closest('div');
        
        // Function to toggle priority visibility based on type
        function togglePriorityVisibility() {
            if (typeSelect.value === 'IOM' || typeSelect.value === '') {
                priorityDiv.style.display = 'block';
            } else {
                priorityDiv.style.display = 'none';
                document.getElementById('priority').value = ''; // Reset priority when hidden
            }
        }
        
        // Initial setting
        togglePriorityVisibility();
        
        // Add event listener for changes
        typeSelect.addEventListener('change', togglePriorityVisibility);
    });
</script>
@endpush