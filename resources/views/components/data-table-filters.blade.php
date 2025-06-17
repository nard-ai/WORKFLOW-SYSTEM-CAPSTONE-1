{{-- Enhanced Data Table Filters Component --}}
@props(['filters' => [], 'currentFilters' => [], 'route' => ''])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
    <div class="p-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-4">
            
            {{-- Search Input --}}
            <div class="flex-1 max-w-md">
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
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Search requests..."
                        value="{{ request('search') }}"
                    >
                </div>
            </div>

            {{-- Filter Controls --}}
            <div class="flex flex-wrap items-center space-x-3">
                
                {{-- Type Filter --}}
                <div class="min-w-0">
                    <select name="type" id="typeFilter" class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Types</option>
                        <option value="IOM" {{ request('type') === 'IOM' ? 'selected' : '' }}>IOM</option>
                        <option value="Leave" {{ request('type') === 'Leave' ? 'selected' : '' }}>Leave</option>
                    </select>
                </div>

                {{-- Date Range Filter --}}
                <div class="min-w-0">
                    <select name="date_range" id="dateRangeFilter" class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="all" {{ request('date_range', 'all') === 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ request('date_range') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_30_days" {{ request('date_range') === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                </div>

                {{-- Priority Filter (if applicable) --}}
                @if(in_array('priority', $filters))
                <div class="min-w-0">
                    <select name="priority" id="priorityFilter" class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">All Priorities</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                @endif

                {{-- Per Page --}}
                <div class="min-w-0">
                    <select name="per_page" id="perPageFilter" class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>

                {{-- Apply/Clear Buttons --}}
                <div class="flex space-x-2">
                    <button type="button" id="applyFilters" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Apply
                    </button>
                    <button type="button" id="clearFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        {{-- Active Filters Display --}}
        @if(request()->hasAny(['search', 'type', 'date_range', 'priority']) && (request('search') || request('type') || request('date_range', 'all') !== 'all' || request('priority')))
        <div class="mt-3 flex items-center space-x-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Active filters:</span>
            <div class="flex flex-wrap gap-2">
                @if(request('search'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Search: "{{ request('search') }}"
                        <button type="button" class="ml-1 text-blue-600 hover:text-blue-800" onclick="removeFilter('search')">×</button>
                    </span>
                @endif
                @if(request('type'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        Type: {{ request('type') }}
                        <button type="button" class="ml-1 text-purple-600 hover:text-purple-800" onclick="removeFilter('type')">×</button>
                    </span>
                @endif
                @if(request('date_range') && request('date_range') !== 'all')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        Date: {{ ucfirst(str_replace('_', ' ', request('date_range'))) }}
                        <button type="button" class="ml-1 text-green-600 hover:text-green-800" onclick="removeFilter('date_range')">×</button>
                    </span>
                @endif
                @if(request('priority'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                        Priority: {{ ucfirst(request('priority')) }}
                        <button type="button" class="ml-1 text-orange-600 hover:text-orange-800" onclick="removeFilter('priority')">×</button>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const dateRangeFilter = document.getElementById('dateRangeFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const perPageFilter = document.getElementById('perPageFilter');
    const applyButton = document.getElementById('applyFilters');
    const clearButton = document.getElementById('clearFilters');

    // Auto-apply filters when changed
    [searchInput, typeFilter, dateRangeFilter, priorityFilter, perPageFilter].forEach(element => {
        if (element) {
            element.addEventListener('change', applyFilters);
        }
    });

    // Search input with debounce
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });
    }

    function applyFilters() {
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        
        // Update URL parameters
        if (searchInput && searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        } else {
            params.delete('search');
        }
        
        if (typeFilter && typeFilter.value) {
            params.set('type', typeFilter.value);
        } else {
            params.delete('type');
        }
        
        if (dateRangeFilter && dateRangeFilter.value && dateRangeFilter.value !== 'all') {
            params.set('date_range', dateRangeFilter.value);
        } else {
            params.delete('date_range');
        }
        
        if (priorityFilter && priorityFilter.value) {
            params.set('priority', priorityFilter.value);
        } else {
            params.delete('priority');
        }
        
        if (perPageFilter && perPageFilter.value && perPageFilter.value !== '10') {
            params.set('per_page', perPageFilter.value);
        } else {
            params.delete('per_page');
        }
        
        // Reset to first page when filtering
        params.delete('page');
        
        // Navigate to new URL
        url.search = params.toString();
        window.location.href = url.toString();
    }

    function clearFilters() {
        const url = new URL(window.location.href);
        const params = new URLSearchParams();
        
        // Keep only tab parameter if it exists
        if (url.searchParams.get('tab')) {
            params.set('tab', url.searchParams.get('tab'));
        }
        
        url.search = params.toString();
        window.location.href = url.toString();
    }

    // Attach clear button event
    if (clearButton) {
        clearButton.addEventListener('click', clearFilters);
    }

    // Apply button event
    if (applyButton) {
        applyButton.addEventListener('click', applyFilters);
    }
});

// Function to remove individual filters
function removeFilter(filterName) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    params.delete(filterName);
    if (filterName !== 'search') {
        params.delete('page'); // Reset pagination when removing filters
    }
    
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
