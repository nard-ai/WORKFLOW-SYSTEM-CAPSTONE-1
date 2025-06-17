{{-- Export Component --}}
@props(['title' => 'Export Data', 'route' => '', 'formats' => ['csv', 'pdf', 'excel']])

<div class="inline-flex items-center" x-data="{ open: false }">
    <div class="relative">
        <button 
            @click="open = !open"
            type="button" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export
            <svg class="ml-2 -mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div 
            x-show="open" 
            @click.away="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute right-0 z-10 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none"
            style="display: none;"
        >
            <div class="py-1">
                <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                    {{ $title }}
                </div>
                
                @if(in_array('csv', $formats))
                <a href="{{ $route }}?format=csv&{{ http_build_query(request()->except('page')) }}" 
                   class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                    <svg class="mr-3 h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export as CSV
                    <span class="ml-auto text-xs text-gray-400">Spreadsheet</span>
                </a>
                @endif

                @if(in_array('pdf', $formats))
                <a href="{{ $route }}?format=pdf&{{ http_build_query(request()->except('page')) }}" 
                   class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                    <svg class="mr-3 h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Export as PDF
                    <span class="ml-auto text-xs text-gray-400">Document</span>
                </a>
                @endif

                @if(in_array('excel', $formats))
                <a href="{{ $route }}?format=excel&{{ http_build_query(request()->except('page')) }}" 
                   class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                    <svg class="mr-3 h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a4 4 0 01-4-4V7a4 4 0 014-4h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a4 4 0 01-4 4z" />
                    </svg>
                    Export as Excel
                    <span class="ml-auto text-xs text-gray-400">Workbook</span>
                </a>
                @endif

                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                
                <button 
                    onclick="printTable()"
                    class="group flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600"
                >
                    <svg class="mr-3 h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Table
                    <span class="ml-auto text-xs text-gray-400">Ctrl+P</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function printTable() {
    // Get the table content
    const table = document.querySelector('table');
    if (!table) return;

    // Create a new window for printing
    const printWindow = window.open('', '_blank');
    
    // Basic styling for print
    const printStyles = `
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f5f5f5; font-weight: bold; }
            .no-print { display: none; }
            @media print {
                body { margin: 0; }
                table { font-size: 12px; }
            }
        </style>
    `;
    
    // Clone the table and remove action columns
    const tableClone = table.cloneNode(true);
    
    // Remove action columns (usually the last column)
    const actionHeaders = tableClone.querySelectorAll('th:last-child');
    const actionCells = tableClone.querySelectorAll('td:last-child');
    
    actionHeaders.forEach(header => {
        if (header.textContent.toLowerCase().includes('action')) {
            header.remove();
        }
    });
    
    actionCells.forEach(cell => {
        const index = Array.from(cell.parentNode.children).indexOf(cell);
        const headerIndex = Array.from(tableClone.querySelector('thead tr').children).length;
        if (index === headerIndex - 1) {
            cell.remove();
        }
    });
    
    // Remove checkboxes
    const checkboxes = tableClone.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => cb.closest('th, td')?.remove());
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>{{ $title }}</title>
            ${printStyles}
        </head>
        <body>
            <h2>{{ $title }}</h2>
            <p>Exported on: ${new Date().toLocaleDateString()}</p>
            ${tableClone.outerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}
</script>
