<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Track Request') }} - ID: {{ $formRequest->form_id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100 space-y-6">
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    {{-- Request Timeline --}}
                    <div class="border-b pb-4 mb-4">
                        <h3 class="text-lg font-semibold mb-4">Request Timeline</h3>
                        @if($formRequest->status === 'Approved')
                            <div class="mb-4 flex justify-end">
                                <a href="{{ route('request.print', $formRequest->form_id) }}" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Print Request
                                </a>
                            </div>
                        @endif
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
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">Request Submitted by {{ $formRequest->requester->username }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $formRequest->date_submitted->format('M j, Y') }} at {{ $formRequest->date_submitted->format('g:i A') }}
                                        </div>
                                    </div>
                                </li>

                                {{-- Approval history --}}
                                @foreach ($formRequest->approvals->sortBy('action_date') as $approval)
                                    @if($approval->action !== 'Submitted')
                                        <li class="flex items-start">
                                            <div class="flex items-center justify-center">
                                                <div class="
                                                    @if($approval->action === 'Rejected') bg-red-500
                                                    @elseif($approval->action === 'Noted') bg-blue-500
                                                    @elseif($approval->action === 'Approved') bg-green-500
                                                    @else bg-gray-500
                                                    @endif
                                                    rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                                    @if($approval->action === 'Rejected')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    @elseif($approval->action === 'Noted')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    @elseif($approval->action === 'Approved')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ml-6">
                                                <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $approval->action }} by {{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}
                                                    @if($approval->approver->position === 'Head')
                                                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(Head)</span>
                                                    @endif
                                                </div>
                                                @if($approval->comments)
                                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                                        "{{ $approval->comments }}"
                                                    </div>
                                                @endif
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ \Carbon\Carbon::parse($approval->action_date)->format('M j, Y') }} at {{ \Carbon\Carbon::parse($approval->action_date)->format('g:i A') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Show Completed status after all approvals --}}
                                @if($formRequest->status === 'Approved')
                                    <li class="flex items-start">
                                        <div class="flex items-center justify-center">
                                            <div class="bg-green-500 rounded-full w-8 h-8 flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-6">
                                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                                Completed
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                {{ \Carbon\Carbon::parse($formRequest->approvals->sortByDesc('action_date')->first()->action_date)->setTimezone('Asia/Manila')->format('M j, Y g:i A') }}
                                            </div>
                                        </div>
                                    </li>
                                @endif

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

                    {{-- IOM Specific Details --}}
                    @if ($formRequest->form_type === 'IOM' && $formRequest->iomDetails)
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold">IOM Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 text-sm">
                                <p><strong>To Department:</strong> {{ $formRequest->toDepartment->dept_name ?? 'N/A' }} ({{ $formRequest->toDepartment->dept_code ?? 'N/A' }})</p>
                                <p><strong>Subject/Re:</strong> {{ $formRequest->title }}</p>
                                <p><strong>Date Needed:</strong> {{ $formRequest->iomDetails->date_needed ? \Carbon\Carbon::parse($formRequest->iomDetails->date_needed)->format('M j, Y') : 'N/A' }}</p>
                                <p><strong>Priority:</strong> {{ $formRequest->iomDetails->priority ?? 'N/A' }}</p>
                                <p class="md:col-span-2"><strong>Purpose:</strong> {{ $formRequest->iomDetails->purpose ?? 'N/A' }}</p>
                                <div class="md:col-span-2">
                                    <p class="font-semibold">Description/Body:</p>
                                    <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700 rounded-md whitespace-pre-wrap">{{ $formRequest->iomDetails->body ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Signatures Section --}}
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Signatures</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($formRequest->approvals->sortBy('action_date') as $approval)
                                    @if($approval->action !== 'Submitted')
                                        <div class="signature-card bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                            @if($approval->signature_data)
                                                <div class="signature-image-container h-20 flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                                                    <img src="{{ $approval->signature_data }}" 
                                                        alt="Digital Signature" 
                                                        class="max-h-16 object-contain">
                                                </div>
                                            @elseif($approval->signature_name && $approval->signatureStyle)
                                                <div class="signature-text-container h-20 flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                                                    <div class="text-xl font-signature" style="font-family: '{{ $approval->signatureStyle->font_family }}', cursive;">
                                                        {{ strtoupper($approval->signature_name) }}
                                                    </div>
                                                </div>
                                            @elseif($approval->signature_name)
                                                <div class="signature-text-container h-20 flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                                                    <div class="text-xl font-signature" style="font-family: 'Dancing Script', cursive;">
                                                        {{ strtoupper($approval->signature_name) }}
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="mt-3 text-center">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}
                                                </p>
                                                <div class="flex items-center justify-center gap-2 mt-1">
                                                    <span class="text-xs px-2 py-1 rounded-full {{ 
                                                        $approval->action === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                        ($approval->action === 'Rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200') 
                                                    }}">
                                                        {{ $approval->action }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ \Carbon\Carbon::parse($approval->action_date)->format('M j, Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Leave Specific Details --}}
                    @if ($formRequest->form_type === 'Leave' && $formRequest->leaveDetails)
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold mb-4">Leave Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="font-medium">Leave Type:</p>
                                    <p class="text-gray-800 dark:text-gray-200">{{ ucfirst($formRequest->leaveDetails->leave_type) }} Leave</p>
                                </div>
                                <div>
                                    <p class="font-medium">Duration:</p>
                                    <p class="text-gray-800 dark:text-gray-200">{{ $formRequest->leaveDetails->days }} day(s)</p>
                                </div>
                                <div>
                                    <p class="font-medium">Start Date:</p>
                                    <p class="text-gray-800 dark:text-gray-200">{{ $formRequest->leaveDetails->start_date->format('F j, Y') }}</p>
                                </div>
                                <div>
                                    <p class="font-medium">End Date:</p>
                                    <p class="text-gray-800 dark:text-gray-200">{{ $formRequest->leaveDetails->end_date->format('F j, Y') }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="font-medium">Description / Reason:</p>
                                    <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap bg-gray-50 dark:bg-gray-700 p-3 rounded-md mt-1">{{ $formRequest->leaveDetails->description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Signatures Section -->
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-semibold mb-4">Signatures</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($formRequest->approvals->sortBy('action_date') as $approval)
                                    @if($approval->action !== 'Submitted')
                                    <div class="border rounded-lg p-4 flex flex-col items-center justify-center">
                                        @if($approval->signature_data)
                                            <img src="{{ $approval->signature_data }}" alt="Digital Signature" class="h-16 mb-2">
                                        @elseif($approval->signature_name && $approval->signatureStyle)
                                            <div class="text-xl font-signature mb-2" style="font-family: '{{ $approval->signatureStyle->font_family }}', cursive;">{{ strtoupper($approval->signature_name) }}</div>
                                        @elseif($approval->signature_name)
                                            <div class="text-xl font-signature mb-2" style="font-family: 'Dancing Script', cursive;">{{ strtoupper($approval->signature_name) }}</div>
                                        @else
                                            <div class="text-xl font-signature mb-2" style="font-family: 'Dancing Script', cursive;">{{ strtoupper($approval->approver->employeeInfo->FirstName . ' ' . $approval->approver->employeeInfo->LastName) }}</div>
                                        @endif
                                        <div class="text-center">
                                            <p class="font-medium">{{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}</p>
                                            <p class="text-sm text-gray-600">
                                                <span class="px-2 py-1 rounded {{ 
                                                    $approval->action === 'Approved' ? 'bg-green-100 text-green-800' : 
                                                    ($approval->action === 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') 
                                                }}">
                                                    {{ $approval->action }}
                                                </span>
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">{{ $approval->action_date->format('M j, Y') }}</p>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Back to Dashboard Link --}}
                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" 
                            class="inline-flex items-center px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg transition-all duration-200 hover:bg-gray-50 hover:text-blue-600 hover:border-blue-300 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Mr+Dafoe&family=Homemade+Apple&family=Pacifico&family=Dancing+Script&display=swap');
</style>