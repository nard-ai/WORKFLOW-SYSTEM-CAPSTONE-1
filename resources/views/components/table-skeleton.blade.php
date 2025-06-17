{{-- Table Loading Skeleton Component --}}
@props(['rows' => 5, 'columns' => 6])

<div class="animate-pulse">
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @for($i = 0; $i < $columns; $i++)
                            <th scope="col" class="px-6 py-3">
                                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-20"></div>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @for($row = 0; $row < $rows; $row++)
                        <tr>
                            @for($col = 0; $col < $columns; $col++)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-{{ ['full', '32', '24', '16'][$col % 4] }}"></div>
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
