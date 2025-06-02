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
                        <div class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-2">
                                @foreach ($formRequest->approvals->sortBy('action_date') as $approval)
                                    @if($approval->action !== 'Submitted' && ($approval->signature_name || $approval->signature_data))
                                        <div class="flex flex-col">
                                            @if($approval->signature_data)
                                                <div class="mb-0.5">
                                                    <img src="{{ $approval->signature_data }}" 
                                                        alt="Digital Signature" 
                                                        class="h-12 object-contain">
                                                </div>
                                            @endif
                                            <div class="space-y-0.5">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $approval->approver->employeeInfo->FirstName }} {{ $approval->approver->employeeInfo->LastName }}</p>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs text-blue-600">{{ $approval->action }}</span>
                                                    <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($approval->action_date)->format('M j, Y') }}</span>
                                                </div>
                                                <div class="pt-0.5 border-t border-gray-200 dark:border-gray-700 w-36"></div>
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
                            <h3 class="text-lg font-semibold">Leave Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 text-sm">
                                <p><strong>Leave Type:</strong> {{ ucfirst($formRequest->leaveDetails->leave_type ?? 'N/A') }}</p>
                                <p><strong>Date of Leave:</strong> {{ $formRequest->leaveDetails->date_of_leave ? \Carbon\Carbon::parse($formRequest->leaveDetails->date_of_leave)->format('M j, Y') : 'N/A' }}</p>
                                <div class="md:col-span-2">
                                    <p class="font-semibold">Reason:</p>
                                    <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-700 rounded-md whitespace-pre-wrap">{{ $formRequest->leaveDetails->description ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Back to Dashboard Link --}}
                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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