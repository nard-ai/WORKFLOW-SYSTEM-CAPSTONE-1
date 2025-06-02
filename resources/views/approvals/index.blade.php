<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Pending Approvals') }}
            </h2>
            <div class="flex space-x-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Average Response Time: {{ $averageResponseTime ?? 'N/A' }}
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    Approval Rate: {{ $approvalRate ?? '0' }}%
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
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

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('approvals.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Request Type</label>
                            <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <option value="">All Types</option>
                                <option value="IOM" {{ request('type') === 'IOM' ? 'selected' : '' }}>IOM</option>
                                <option value="Leave" {{ request('type') === 'Leave' ? 'selected' : '' }}>Leave</option>
                            </select>
                        </div>
                        <div>
                            <label for="date_range" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                            <select name="date_range" id="date_range" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="all" {{ request('date_range') === 'all' ? 'selected' : '' }}>All Time</option>
                            </select>
                        </div>
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                            <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <option value="">All Priorities</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Requests Awaiting Your Action</h3>
                        @if(!$requestsToApprove->isEmpty())
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

                    @if($requestsToApprove->isEmpty())
                        <p>You have no requests awaiting your action.</p>
                    @else
                        <form id="batchForm" method="POST" action="{{ route('approvals.batch') }}">
                            @csrf
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left">
                                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600">
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requester</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title/Subject</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Submitted</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Wait Time</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($requestsToApprove as $request)
                                            <tr class="{{ $request->is_overdue ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                                                <td class="px-6 py-4">
                                                    <input type="checkbox" name="selected_requests[]" value="{{ $request->form_id }}" 
                                                           class="request-checkbox rounded border-gray-300 dark:border-gray-600">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->form_id }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $request->form_type === 'IOM' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                        {{ $request->form_type }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $request->requester->username ?? 'N/A' }} 
                                                    <span class="text-xs text-gray-500">({{ $request->requester->department->dept_code ?? 'N/A' }})</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    <div class="flex items-center">
                                                        @if($request->priority === 'high')
                                                            <span class="mr-2 text-red-500">
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                                                                </svg>
                                                            </span>
                                                        @endif
                                                        {{ Str::limit($request->title, 30) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $request->date_submitted->format('M j, Y H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <span class="text-gray-500 dark:text-gray-400">
                                                        {{ $request->wait_time }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if(str_contains(strtolower($request->status), 'pending'))
                                                            bg-yellow-100 text-yellow-800
                                                        @elseif(str_contains(strtolower($request->status), 'approved'))
                                                            bg-green-100 text-green-800
                                                        @elseif(str_contains(strtolower($request->status), 'rejected'))
                                                            bg-red-100 text-red-800
                                                        @elseif(str_contains(strtolower($request->status), 'noted'))
                                                            bg-blue-100 text-blue-800
                                                        @else
                                                            bg-gray-100 text-gray-800
                                                        @endif
                                                    ">
                                                        {{ $request->status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('approvals.show', $request) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">View/Action</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                        
                        @if(method_exists($requestsToApprove, 'links'))
                            <div class="mt-4">
                                {{ $requestsToApprove->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Action Modal -->
    <div id="batchActionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" id="modalTitle">Batch Action</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400" id="modalMessage"></p>
                    <div class="mt-4">
                        <div class="flex justify-between items-center">
                            <label for="batchComment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Comments <span id="commentRequired" class="text-red-500 hidden">*</span>
                            </label>
                            <span id="commentError" class="text-sm text-red-500 hidden">Comments are required for rejection</span>
                        </div>
                        <textarea id="batchComment" name="comment" rows="3" 
                                 class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeBatchModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancel
                    </button>
                    <button type="button" onclick="submitBatchAction()"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const requestCheckboxes = document.querySelectorAll('.request-checkbox');
    const batchApproveBtn = document.getElementById('batchApproveBtn');
    const batchRejectBtn = document.getElementById('batchRejectBtn');
    const batchActionModal = document.getElementById('batchActionModal');
    const commentRequired = document.getElementById('commentRequired');
    const commentError = document.getElementById('commentError');
    const batchComment = document.getElementById('batchComment');
    let currentAction = '';

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

    function updateSelectAllState() {
        const allChecked = Array.from(requestCheckboxes).every(checkbox => checkbox.checked);
        const someChecked = Array.from(requestCheckboxes).some(checkbox => checkbox.checked);
        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
    }

    function updateBatchButtonsState() {
        const hasSelection = Array.from(requestCheckboxes).some(checkbox => checkbox.checked);
        batchApproveBtn.disabled = !hasSelection;
        batchRejectBtn.disabled = !hasSelection;
        batchApproveBtn.classList.toggle('opacity-50', !hasSelection);
        batchRejectBtn.classList.toggle('opacity-50', !hasSelection);
    }

    if (batchApproveBtn) {
        batchApproveBtn.addEventListener('click', () => openBatchModal('approve'));
    }

    if (batchRejectBtn) {
        batchRejectBtn.addEventListener('click', () => openBatchModal('reject'));
    }

    window.openBatchModal = function(action) {
        currentAction = action;
        const selectedCount = document.querySelectorAll('.request-checkbox:checked').length;
        document.getElementById('modalTitle').textContent = `Batch ${action.charAt(0).toUpperCase() + action.slice(1)}`;
        document.getElementById('modalMessage').textContent = `Are you sure you want to ${action} ${selectedCount} selected requests?`;
        
        // Show/hide required indicator and reset error state
        commentRequired.classList.toggle('hidden', action !== 'reject');
        commentError.classList.add('hidden');
        batchComment.classList.remove('border-red-500');
        
        // Clear previous comment
        batchComment.value = '';
        
        batchActionModal.classList.remove('hidden');
    }

    window.closeBatchModal = function() {
        batchActionModal.classList.add('hidden');
        batchComment.value = '';
        commentError.classList.add('hidden');
        batchComment.classList.remove('border-red-500');
    }

    window.submitBatchAction = function() {
        const form = document.getElementById('batchForm');
        const comment = batchComment.value.trim();
        
        // Validate comment for rejection
        if (currentAction === 'reject' && !comment) {
            commentError.classList.remove('hidden');
            batchComment.classList.add('border-red-500');
            batchComment.focus();
            return;
        }

        // Hide error state if validation passes
        commentError.classList.add('hidden');
        batchComment.classList.remove('border-red-500');
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = currentAction;
        form.appendChild(actionInput);

        const commentInput = document.createElement('input');
        commentInput.type = 'hidden';
        commentInput.name = 'comment';
        commentInput.value = comment;
        form.appendChild(commentInput);

        form.submit();
    }

    // Add input event listener to comment field to clear error state when typing
    batchComment.addEventListener('input', function() {
        if (this.value.trim()) {
            commentError.classList.add('hidden');
            this.classList.remove('border-red-500');
        }
    });

    // Initialize button states
    updateBatchButtonsState();
});
</script> 