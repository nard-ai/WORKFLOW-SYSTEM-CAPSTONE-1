<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Review Request Details') }} - ID: {{ $formRequest->form_id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100 space-y-6">
                    {{-- Request Timeline --}}
                    <div class="border-b pb-4 mb-4">
                        <h3 class="text-lg font-semibold mb-4">Request Timeline</h3>
                        <div class="relative">
                            {{-- Timeline line --}}
                            <div class="absolute h-full w-1 bg-gradient-to-b from-blue-500 via-blue-300 to-gray-200 left-4 top-4"></div>
                            
                            <ul class="space-y-8 relative">
                                {{-- Initial submission --}}
                                <li class="flex items-start">
                                    <div class="flex items-center justify-center">
                                        <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-6">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">Submitted by {{ $formRequest->requester->username }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $formRequest->date_submitted->format('M j, Y') }} at {{ $formRequest->date_submitted->format('g:i A') }}
                                        </div>
                                    </div>
                                </li>

                                {{-- Approval history --}}
                                @foreach ($formRequest->approvals->sortBy('action_date') as $approval)
                                    @if($approval->action !== 'Submitted' && $approval->action !== 'Approved')
                                        <li class="flex items-start">
                                            <div class="flex items-center justify-center">
                                                <div class="
                                                    @if($approval->action === 'Rejected') bg-red-500
                                                    @elseif($approval->action === 'Noted') bg-blue-500
                                                    @else bg-gray-500
                                                    @endif
                                                    rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800
                                                ">
                                                    @if($approval->action === 'Rejected')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    @elseif($approval->action === 'Noted')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-6">
                                                <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $approval->action }}
                                                </div>
                                                @if($approval->comments)
                                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                                        "{{ $approval->comments }}"
                                                    </div>
                                                @endif
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ \Carbon\Carbon::parse($approval->action_date)->setTimezone('Asia/Manila')->format('M j, Y') }} at {{ \Carbon\Carbon::parse($approval->action_date)->setTimezone('Asia/Manila')->format('g:i A') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Current status if not completed --}}
                                @if(!in_array($formRequest->status, ['Approved', 'Rejected', 'Cancelled']))
                                    <li class="flex items-start">
                                        <div class="flex items-center justify-center">
                                            <div class="bg-yellow-500 rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-6">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">Currently {{ $formRequest->status }}</div>
                                            @if($currentApprover = App\Models\User::find($formRequest->current_approver_id))
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Awaiting action from {{ $currentApprover->username }}
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{-- Request Details --}}
                    <div class="border-b pb-4 mb-4">
                        <h3 class="text-lg font-semibold">Request Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 text-sm">
                            <p><strong>Request ID:</strong> {{ $formRequest->form_id }}</p>
                            <p><strong>Type:</strong> {{ $formRequest->form_type }}</p>
                            <p><strong>Requester:</strong> {{ $formRequest->requester->username ?? 'N/A' }}</p>
                            <p><strong>Requester's Dept:</strong> {{ $formRequest->requester->department->dept_name ?? 'N/A' }} ({{ $formRequest->requester->department->dept_code ?? 'N/A' }})</p>
                            <p><strong>Date Submitted:</strong> {{ $formRequest->date_submitted->format('Y-m-d H:i A') }}</p>
                            <p><strong>Current Status:</strong> <span class="font-semibold">{{ $formRequest->status }}</span></p>
                        </div>
                    </div>

                    {{-- IOM Specific Details --}}
                    @if ($formRequest->form_type === 'IOM' && $formRequest->iomDetails)
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold">IOM Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 text-sm">
                                <p><strong>To Department:</strong> {{ $formRequest->toDepartment->dept_name ?? 'N/A' }} ({{ $formRequest->toDepartment->dept_code ?? 'N/A' }})</p>
                                <p><strong>Subject/Re:</strong> {{ $formRequest->title }}</p>
                                <p><strong>Date Needed:</strong> {{ $formRequest->iomDetails->date_needed ? \Carbon\Carbon::parse($formRequest->iomDetails->date_needed)->format('Y-m-d') : 'N/A' }}</p>
                                <p><strong>Priority:</strong> {{ $formRequest->iomDetails->priority ?? 'N/A' }}</p>
                                <p class="md:col-span-2"><strong>Purpose:</strong> {{ $formRequest->iomDetails->purpose ?? 'N/A' }}</p>
                                <div class="md:col-span-2">
                                    <p class="font-semibold">Description/Body:</p>
                                    <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700 rounded-md whitespace-pre-wrap">{{ $formRequest->iomDetails->body ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Leave Specific Details --}}
                    @if ($formRequest->form_type === 'Leave' && $formRequest->leaveDetails)
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold">Leave Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 text-sm">
                                <p><strong>Leave Type:</strong> {{ ucfirst($formRequest->leaveDetails->leave_type ?? 'N/A') }}</p>
                                <p><strong>Date of Leave:</strong> {{ $formRequest->leaveDetails->date_of_leave ? \Carbon\Carbon::parse($formRequest->leaveDetails->date_of_leave)->format('Y-m-d') : 'N/A' }}</p>
                                <div class="md:col-span-2">
                                    <p class="font-semibold">Reason:</p>
                                    <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700 rounded-md whitespace-pre-wrap">{{ $formRequest->leaveDetails->description ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Approval Actions --}}
                    @if(Auth::id() === $formRequest->current_approver_id || 
                        (Auth::user()->position === 'Head' && 
                         Auth::user()->department_id === $formRequest->requester->department_id && 
                         $formRequest->status === 'Pending'))
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold mb-4">Take Action</h3>
                            <div class="flex space-x-4">
                                @if($formRequest->form_type === 'IOM')
                                    @if($formRequest->status === 'Pending' && Auth::user()->position === 'Head')
                                        {{-- Note Button for department head --}}
                                        <button onclick="openActionModal('note')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                            Note
                                        </button>
                                    @endif

                                    @if(($formRequest->status === 'Pending Target Department Approval' || $formRequest->status === 'In Progress') && 
                                        Auth::user()->position === 'Head' && 
                                        Auth::user()->department_id === $formRequest->to_department_id)
                                        {{-- Approve Button for target department head --}}
                                        <button onclick="openActionModal('approve')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                            Approve
                                        </button>
                                    @endif
                                @else
                                    {{-- For non-IOM requests --}}
                                    <button onclick="openActionModal('approve')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                        Approve
                                    </button>
                                @endif

                                {{-- Reject Button (always available) --}}
                                <button onclick="openActionModal('reject')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                                    Reject
                                </button>
                            </div>
                        </div>

                        {{-- Action Modal --}}
                        <div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                            <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white dark:bg-gray-800">
                                <div class="mt-3">
                                    <h3 id="modalTitle" class="text-lg font-medium text-gray-900 mb-4"></h3>
                                    <form id="actionForm" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                                Type your full name
                                            </label>
                                            <input type="text" 
                                                id="name" 
                                                name="name" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 uppercase"
                                                style="text-transform: uppercase;"
                                                placeholder="Your legal name"
                                                value="{{ Auth::user()->employeeInfo->FirstName }} {{ Auth::user()->employeeInfo->LastName }}"
                                                required>
                                        </div>

                                        {{-- Signature Selection --}}
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Choose your signature style
                                            </label>
                                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4">
                                                <div id="signatureStyles" class="grid grid-cols-2 gap-4">
                                                    {{-- Signature styles will be loaded here --}}
                                                </div>
                                                <div class="mt-3 text-xs text-gray-500 text-center">
                                                    Select a style and your name will be converted to a signature
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" id="signature" name="signature">
                                        <input type="hidden" id="signatureStyle" name="signatureStyle">

                                        {{-- Comments Section --}}
                                        <div class="mb-4">
                                            <div class="flex justify-between items-center">
                                                <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">
                                                    Comments <span id="commentRequired" class="text-red-500 hidden">*</span>
                                                </label>
                                                <span id="commentError" class="text-sm text-red-500 hidden">Comments are required for rejection</span>
                                            </div>
                                            <textarea id="comments" 
                                                name="comments" 
                                                rows="3" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="flex justify-end space-x-3 mt-6">
                                            <button type="button" 
                                                onclick="closeModal()"
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                                                Cancel
                                            </button>
                                            <button type="submit" 
                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                                                Submit
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('approvals.index') }}" 
                            class="inline-flex items-center px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg transition-all duration-200 hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Approvals List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Add these styles to your layout or in a style tag --}}
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
    let actionModal = document.getElementById('actionModal');
    let actionForm = document.getElementById('actionForm');
    let modalTitle = document.getElementById('modalTitle');
    let selectedStyle = null;
    let currentAction = '';
    const commentRequired = document.getElementById('commentRequired');
    const commentError = document.getElementById('commentError');
    const comments = document.getElementById('comments');

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
        ctx.fillText(text, canvas.width / 2, canvas.height / 2);
        
        return canvas.toDataURL('image/png');
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
                const currentName = document.getElementById('name').value;
                if (currentName) {
                    updateAllPreviews(currentName);
                }
            });
    }

    function openActionModal(action) {
        currentAction = action;
        let formAction = '';
        switch(action) {
            case 'note':
                modalTitle.textContent = 'Add Note to Request';
                formAction = '{{ route("approvals.note", $formRequest->form_id) }}';
                break;
            case 'approve':
                modalTitle.textContent = 'Approve Request';
                formAction = '{{ route("approvals.approve", $formRequest->form_id) }}';
                break;
            case 'reject':
                modalTitle.textContent = 'Reject Request';
                formAction = '{{ route("approvals.reject", $formRequest->form_id) }}';
                break;
        }
        
        commentRequired.classList.toggle('hidden', action !== 'reject');
        commentError.classList.add('hidden');
        comments.classList.remove('border-red-500');
        
        // Set the user's full name automatically
        const fullName = '{{ Auth::user()->employeeInfo->FirstName }} {{ Auth::user()->employeeInfo->LastName }}';
        document.getElementById('name').value = fullName.toUpperCase();
        
        actionForm.action = formAction;
        actionModal.classList.remove('hidden');
        loadSignatureStyles();
        
        // Trigger the preview update with the full name
        updateAllPreviews(fullName);
    }

    function closeModal() {
        actionModal.classList.add('hidden');
        comments.value = '';
        commentError.classList.add('hidden');
        comments.classList.remove('border-red-500');
        selectedStyle = null;
    }

    actionForm.addEventListener('submit', function(e) {
        if (currentAction === 'reject' && !comments.value.trim()) {
            e.preventDefault();
            commentError.classList.remove('hidden');
            comments.classList.add('border-red-500');
            comments.focus();
            return;
        }
        
        if (!selectedStyle || !document.getElementById('name').value.trim()) {
            e.preventDefault();
            alert('Please enter your name and select a signature style.');
            return;
        }

        // Convert signature text to image (already in uppercase)
        const name = document.getElementById('name').value;
        const signatureImage = textToImage(name, selectedStyle.fontFamily);
        document.getElementById('signature').value = signatureImage;
    });

    comments.addEventListener('input', function() {
        if (this.value.trim()) {
            commentError.classList.add('hidden');
            this.classList.remove('border-red-500');
        }
    });

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
        
        selectedStyle = { id: styleId, fontFamily: fontFamily, element: element };
        element.classList.add('selected');
        document.getElementById('signatureStyle').value = styleId;
        
        // Update signature preview with uppercase name
        const name = document.getElementById('name').value.toUpperCase();
        updateAllPreviews(name);
    }

    // Name input handler - updates all previews dynamically and ensures uppercase
    document.getElementById('name').addEventListener('input', function() {
        const upperName = this.value.toUpperCase();
        // Only update the input value if it's different to avoid cursor jumping
        if (this.value !== upperName) {
            this.value = upperName;
        }
        updateAllPreviews(upperName);
    });
</script>