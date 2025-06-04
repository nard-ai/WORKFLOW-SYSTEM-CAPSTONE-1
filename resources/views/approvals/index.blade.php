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
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Requests Awaiting Action</h3>
                                @if(!$requestsToApprove->isEmpty() && Auth::user()->accessRole === 'Approver')
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
                                <div class="text-center py-8">
                                    <p class="text-gray-500 dark:text-gray-400">No requests currently need your action.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                                                </th>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requester</th>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title/Subject</th>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Submitted</th>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Wait Time</th>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                            @foreach($requestsToApprove as $request)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if(Auth::user()->accessRole === 'Approver')
                                                            <input type="checkbox" name="selected_requests[]" value="{{ $request->form_id }}" class="request-checkbox rounded border-gray-300 dark:border-gray-600 text-indigo-600">
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->form_id }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            {{ $request->form_type === 'IOM' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                                            {{ $request->form_type }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $request->requester->employeeInfo->FirstName }} {{ $request->requester->employeeInfo->LastName }}
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $request->requester->department->dept_name }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $request->title }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $request->date_submitted->format('M j, Y g:i A') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $request->date_submitted->diffForHumans() }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            {{ $request->status === 'Pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                                               ($request->status === 'In Progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                               'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200') }}"
                                                            data-status="{{ $request->status }}">
                                                            {{ $request->status }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a href="{{ route('approvals.show', $request) }}" 
                                                           class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                            View/Action
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
                        Type your full name
                    </label>
                    <input type="text" 
                        id="fullName" 
                        name="fullName" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                        style="text-transform: uppercase;"
                        placeholder="Your legal name"
                        value="{{ Auth::user()->employeeInfo->FirstName }} {{ Auth::user()->employeeInfo->LastName }}"
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
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
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
    
    ctx.font = `${calculatedSize}px ${fontFamily}`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    // Add subtle shadow
    ctx.shadowColor = 'rgba(0, 0, 0, 0.1)';
    ctx.shadowBlur = 2;
    ctx.shadowOffsetY = 2;
    
    // Draw text
    ctx.fillText(text.toUpperCase(), canvas.width / 2, canvas.height / 2);
    
    return canvas.toDataURL('image/png');
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
    const selectedRequests = Array.from(document.querySelectorAll('.request-checkbox:checked')).map(cb => cb.value);
    const action = document.getElementById('batchAction').value;
    const comment = document.getElementById('batchComment').value;
    const fullName = document.getElementById('fullName').value;
    
    // Get selected signature style
    const selectedStyle = document.querySelector('.signature-style.selected');
    if (!selectedStyle) {
        document.getElementById('signatureError').classList.remove('hidden');
        return;
    }
    
    // Validate comment for rejection
    if (action === 'reject' && !comment.trim()) {
        document.getElementById('commentError').classList.remove('hidden');
        document.getElementById('batchComment').classList.add('border-red-500');
        return;
    }

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

    // Send request
    fetch('{{ route("approvals.batch") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', data.message);
            closeBatchModal();
            // Reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showToast('error', data.message);
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                console.error(errorMessages);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'An error occurred while processing the request.');
    });
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

    function updateSelectAllState() {
        if (!selectAll) return;
        const allChecked = Array.from(requestCheckboxes).every(checkbox => checkbox.checked);
        const someChecked = Array.from(requestCheckboxes).some(checkbox => checkbox.checked);
        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked && !allChecked;
    }

    function updateBatchButtonsState() {
        if (!batchApproveBtn || !batchRejectBtn) return;
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

    // Load signature styles
    function loadSignatureStyles() {
        fetch('{{ route("signature-styles.index") }}')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('signatureStyles');
                container.innerHTML = '';
                data.forEach(style => {
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
            });
    }

    // Function to update all signature previews
    function updateAllPreviews(name) {
        const displayText = name.trim() ? name.toUpperCase() : 'Your Signature';
        document.querySelectorAll('.preview-text').forEach(preview => {
            preview.textContent = displayText;
        });
    }

    function selectStyle(styleId, fontFamily, element) {
        // Update selection state
        document.querySelectorAll('.signature-style').forEach(div => {
            div.classList.remove('selected');
        });
        element.classList.add('selected');
        selectedSignatureId = styleId;
        selectedFontFamily = fontFamily;
        signatureError.classList.add('hidden');
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

        if (isHead) {
            return { valid: true, message: '' }; // Department heads can approve all
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
        
        modalTitle.textContent = `Are you sure you want to ${action} ${selectedCount} selected request${selectedCount > 1 ? 's' : ''}?`;
        
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
});
</script> 