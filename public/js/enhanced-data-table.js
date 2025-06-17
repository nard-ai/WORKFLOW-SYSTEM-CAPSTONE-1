// Enhanced Data Table JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeDataTables();
});

function initializeDataTables() {
    // Auto-save filter preferences
    saveFilterPreferences();
    
    // Enhanced keyboard navigation
    setupKeyboardNavigation();
    
    // Improved loading states
    setupLoadingStates();
    
    // Auto-refresh functionality
    setupAutoRefresh();
    
    // Enhanced accessibility
    setupAccessibility();
}

// Save user filter preferences to localStorage
function saveFilterPreferences() {
    const filters = ['search', 'type', 'date_range', 'priority', 'per_page'];
    const currentFilters = {};
    
    filters.forEach(filter => {
        const element = document.querySelector(`[name="${filter}"]`);
        if (element && element.value) {
            currentFilters[filter] = element.value;
        }
    });
    
    if (Object.keys(currentFilters).length > 0) {
        localStorage.setItem('table_filters_' + window.location.pathname, JSON.stringify(currentFilters));
    }
}

// Load saved filter preferences
function loadFilterPreferences() {
    const saved = localStorage.getItem('table_filters_' + window.location.pathname);
    if (saved) {
        try {
            const filters = JSON.parse(saved);
            Object.keys(filters).forEach(key => {
                const element = document.querySelector(`[name="${key}"]`);
                if (element) {
                    element.value = filters[key];
                }
            });
        } catch (e) {
            console.warn('Could not load filter preferences:', e);
        }
    }
}

// Enhanced keyboard navigation
function setupKeyboardNavigation() {
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
                searchInput.value = '';
                searchInput.blur();
                // Trigger search clear
                const event = new Event('input', { bubbles: true });
                searchInput.dispatchEvent(event);
            }
        }
        
        // Arrow keys for table navigation
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            const activeRow = document.querySelector('.table-row-selected');
            if (activeRow) {
                e.preventDefault();
                const rows = Array.from(document.querySelectorAll('tbody tr'));
                const currentIndex = rows.indexOf(activeRow);
                
                let nextIndex;
                if (e.key === 'ArrowDown') {
                    nextIndex = Math.min(currentIndex + 1, rows.length - 1);
                } else {
                    nextIndex = Math.max(currentIndex - 1, 0);
                }
                
                if (rows[nextIndex]) {
                    activeRow.classList.remove('table-row-selected');
                    rows[nextIndex].classList.add('table-row-selected');
                    rows[nextIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        }
    });
}

// Loading states with better UX
function setupLoadingStates() {
    // Show loading state for form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            showTableLoading();
        });
    });
    
    // Show loading state for pagination links
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!e.defaultPrevented) {
                showTableLoading();
            }
        });
    });
}

function showTableLoading() {
    const tableContainer = document.querySelector('.table-container, .responsive-table');
    if (tableContainer) {
        // Create loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 flex items-center justify-center z-10';
        loadingOverlay.innerHTML = `
            <div class="flex items-center space-x-2">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Loading...</span>
            </div>
        `;
        
        // Make container relative if not already
        if (getComputedStyle(tableContainer).position === 'static') {
            tableContainer.style.position = 'relative';
        }
        
        tableContainer.appendChild(loadingOverlay);
        
        // Remove after 5 seconds as fallback
        setTimeout(() => {
            if (loadingOverlay.parentNode) {
                loadingOverlay.remove();
            }
        }, 5000);
    }
}

// Auto-refresh functionality (optional)
function setupAutoRefresh() {
    const autoRefreshCheckbox = document.getElementById('autoRefresh');
    const autoRefreshInterval = document.getElementById('autoRefreshInterval');
    let refreshTimer;
    
    if (autoRefreshCheckbox) {
        autoRefreshCheckbox.addEventListener('change', function() {
            if (this.checked) {
                const interval = (autoRefreshInterval?.value || 30) * 1000;
                refreshTimer = setInterval(() => {
                    window.location.reload();
                }, interval);
            } else {
                clearInterval(refreshTimer);
            }
        });
    }
}

// Enhanced accessibility features
function setupAccessibility() {
    // Add ARIA labels and roles
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        table.setAttribute('role', 'table');
        table.setAttribute('aria-label', 'Data table');
        
        // Add row and column headers
        const headers = table.querySelectorAll('thead th');
        headers.forEach((header, index) => {
            header.setAttribute('scope', 'col');
            header.setAttribute('aria-sort', 'none');
        });
        
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.setAttribute('role', 'row');
            row.setAttribute('aria-rowindex', index + 1);
            
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, cellIndex) => {
                cell.setAttribute('role', 'cell');
                cell.setAttribute('aria-describedby', `col-${cellIndex}`);
            });
        });
    });
    
    // Announce filter changes to screen readers
    const filterElements = document.querySelectorAll('[name="search"], [name="type"], [name="date_range"], [name="priority"]');
    filterElements.forEach(element => {
        element.addEventListener('change', function() {
            announceToScreenReader(`Filter changed: ${this.name} set to ${this.value || 'all'}`);
        });
    });
}

// Screen reader announcements
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    
    document.body.appendChild(announcement);
    
    setTimeout(() => {
        document.body.removeChild(announcement);
    }, 1000);
}

// Enhanced batch operations
function setupBatchOperations() {
    const selectAllCheckbox = document.getElementById('selectAllRows');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const batchButtons = document.querySelectorAll('[data-batch-action]');
    
    // Enhanced select all with indeterminate state
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                updateRowSelection(checkbox.closest('tr'), isChecked);
            });
            updateBatchActionState();
        });
    }
    
    // Individual checkbox changes
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateRowSelection(this.closest('tr'), this.checked);
            updateSelectAllState();
            updateBatchActionState();
        });
    });
    
    // Batch action confirmations
    batchButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const action = this.dataset.batchAction;
            const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
            
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one item.');
                return;
            }
            
            const message = `Are you sure you want to ${action} ${selectedCount} item(s)?`;
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

function updateRowSelection(row, isSelected) {
    if (isSelected) {
        row.classList.add('table-row-selected');
    } else {
        row.classList.remove('table-row-selected');
    }
}

function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('selectAllRows');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
    
    if (selectAllCheckbox) {
        if (checkedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === rowCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }
}

function updateBatchActionState() {
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
    const batchActionsBar = document.getElementById('batchActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (batchActionsBar) {
        if (checkedCount > 0) {
            batchActionsBar.classList.remove('hidden');
            if (selectedCount) {
                selectedCount.textContent = checkedCount;
            }
        } else {
            batchActionsBar.classList.add('hidden');
        }
    }
}

// Initialize batch operations when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupBatchOperations();
});

// Export utility functions
window.DataTableUtils = {
    showLoading: showTableLoading,
    announceToScreenReader: announceToScreenReader,
    saveFilterPreferences: saveFilterPreferences,
    loadFilterPreferences: loadFilterPreferences
};
