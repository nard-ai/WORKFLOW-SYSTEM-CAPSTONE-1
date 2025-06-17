{{-- Mobile Responsive Table Component --}}
@props([
    'headers' => [],
    'rows' => [],
    'mobileLabels' => [],
    'hideOnMobile' => [],
    'showOnMobile' => [],
    'cardView' => true
])

@php
    $mobileLabels = $mobileLabels ?: $headers;
@endphp

<div class="responsive-table">
    {{-- Desktop/Tablet Table View --}}
    <div class="hidden sm:block">
        <div class="overflow-x-auto table-container">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @foreach($headers as $key => $header)
                            @if(!in_array($key, $hideOnMobile))
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ $header }}
                                </th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($rows as $row)
                        <tr class="data-table-row hover:bg-gray-50 dark:hover:bg-gray-700">
                            @foreach($headers as $key => $header)
                                @if(!in_array($key, $hideOnMobile))
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {!! $row[$key] ?? '' !!}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Card View --}}
    @if($cardView)
    <div class="sm:hidden space-y-4">
        @forelse($rows as $index => $row)
            <div class="mobile-table-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                @foreach($headers as $key => $header)
                    @if(empty($showOnMobile) || in_array($key, $showOnMobile))
                        <div class="mobile-table-cell flex justify-between items-start py-2 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                            <span class="mobile-table-label font-medium text-gray-500 dark:text-gray-400 text-sm">
                                {{ $mobileLabels[$key] ?? $header }}:
                            </span>
                            <span class="mobile-table-value text-gray-900 dark:text-gray-100 text-sm text-right max-w-[60%]">
                                {!! $row[$key] ?? '' !!}
                            </span>
                        </div>
                    @endif
                @endforeach
                
                {{-- Actions for mobile --}}
                @if(isset($row['actions']) && !empty($row['actions']))
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex justify-end space-x-2">
                            {!! $row['actions'] !!}
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No data available</p>
            </div>
        @endforelse
    </div>
    @else
    {{-- Mobile List View (Alternative) --}}
    <div class="sm:hidden">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            @forelse($rows as $index => $row)
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                    <div class="space-y-1">
                        @foreach($headers as $key => $header)
                            @if(empty($showOnMobile) || in_array($key, $showOnMobile))
                                <div class="text-sm">
                                    <span class="font-medium text-gray-500 dark:text-gray-400">{{ $mobileLabels[$key] ?? $header }}:</span>
                                    <span class="text-gray-900 dark:text-gray-100 ml-1">{!! $row[$key] ?? '' !!}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    
                    @if(isset($row['actions']) && !empty($row['actions']))
                        <div class="mt-2 flex justify-end">
                            {!! $row['actions'] !!}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No data available</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif
</div>

<style>
/* Mobile optimizations */
@media (max-width: 640px) {
    .mobile-table-value {
        word-break: break-word;
        hyphens: auto;
    }
    
    .mobile-table-card {
        margin-bottom: 1rem;
    }
    
    .mobile-table-cell {
        min-height: 2.5rem;
        align-items: flex-start;
    }
    
    /* Hide unnecessary elements on mobile */
    .hide-mobile {
        display: none !important;
    }
    
    /* Adjust button sizes for mobile */
    .mobile-table-card .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
}

/* Smooth transitions for responsive changes */
.responsive-table > div {
    transition: all 0.3s ease-in-out;
}
</style>
