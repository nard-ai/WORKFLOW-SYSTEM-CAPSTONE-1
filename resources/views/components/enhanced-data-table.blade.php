{{-- Enhanced Data Table Component --}}
@props([
    'headers' => [],
    'rows' => [],
    'pagination' => null,
    'sortable' => [],
    'currentSort' => null,
    'currentDirection' => 'asc',
    'showCheckboxes' => false,
    'batchActions' => false,
    'emptyMessage' => 'No data available',
    'rowActions' => []
])

<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
    
    {{-- Batch Actions Bar (when items are selected) --}}
    @if($showCheckboxes && $batchActions)
    <div id="batchActionsBar" class="hidden bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800 px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span id="selectedCount" class="text-sm font-medium text-blue-900 dark:text-blue-100"></span>
                <span class="text-sm text-blue-700 dark:text-blue-300 ml-1">selected</span>
            </div>
            <div class="flex space-x-2">
                {{ $batchActions }}
            </div>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    @if($showCheckboxes)
                        <th scope="col" class="px-6 py-3 text-left">
                            <input type="checkbox" id="selectAllRows" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        </th>
                    @endif
                    
                    @foreach($headers as $key => $header)
                        @php
                            $sortableColumn = in_array($key, $sortable);
                            $isCurrentSort = $currentSort === $key;
                            $nextDirection = $isCurrentSort && $currentDirection === 'asc' ? 'desc' : 'asc';
                        @endphp
                        
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            @if($sortableColumn)
                                <button 
                                    onclick="sortTable('{{ $key }}', '{{ $nextDirection }}')"
                                    class="group inline-flex items-center space-x-1 text-left hover:text-gray-900 dark:hover:text-gray-100"
                                >
                                    <span>{{ $header }}</span>
                                    <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-500">
                                        @if($isCurrentSort)
                                            @if($currentDirection === 'asc')
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="h-4 w-4 opacity-0 group-hover:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </button>
                            @else
                                {{ $header }}
                            @endif
                        </th>
                    @endforeach
                    
                    @if(!empty($rowActions))
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($rows as $index => $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        @if($showCheckboxes)
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="selected_items[]" value="{{ $row['id'] ?? $index }}" class="row-checkbox rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                            </td>
                        @endif
                        
                        @foreach($headers as $key => $header)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {!! $row[$key] ?? '' !!}
                            </td>
                        @endforeach
                        
                        @if(!empty($rowActions))
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center space-x-2">
                                    @foreach($rowActions as $action)
                                        {!! str_replace(['{{row}}', '{{index}}'], [$row, $index], $action) !!}
                                    @endforeach
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) + ($showCheckboxes ? 1 : 0) + (!empty($rowActions) ? 1 : 0) }}" class="px-6 py-12 text-center">
                            <div class="text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-lg font-medium">{{ $emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pagination && $pagination->hasPages())
        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($pagination->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                            Previous
                        </span>
                    @else
                        <a href="{{ $pagination->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                            Previous
                        </a>
                    @endif

                    @if($pagination->hasMorePages())
                        <a href="{{ $pagination->nextPageUrl() }}" class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                            Next
                        </a>
                    @else
                        <span class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                            Next
                        </span>
                    @endif
                </div>

                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Showing
                            <span class="font-medium">{{ $pagination->firstItem() ?: 0 }}</span>
                            to
                            <span class="font-medium">{{ $pagination->lastItem() ?: 0 }}</span>
                            of
                            <span class="font-medium">{{ $pagination->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            {{ $pagination->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllRows');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const batchActionsBar = document.getElementById('batchActionsBar');
    const selectedCount = document.getElementById('selectedCount');

    // Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBatchActions();
        });
    }

    // Individual checkbox change
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBatchActions();
            
            // Update select all checkbox state
            if (selectAllCheckbox) {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
            }
        });
    });

    function updateBatchActions() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        
        if (batchActionsBar && selectedCount) {
            if (checkedBoxes.length > 0) {
                batchActionsBar.classList.remove('hidden');
                selectedCount.textContent = checkedBoxes.length;
            } else {
                batchActionsBar.classList.add('hidden');
            }
        }
    }
});

// Sorting function
function sortTable(column, direction) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    params.set('sort', column);
    params.set('direction', direction);
    params.delete('page'); // Reset to first page when sorting
    
    url.search = params.toString();
    window.location.href = url.toString();
}
</script>
