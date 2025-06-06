<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Confirm Your Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(empty($formData))
                        <p>No data to confirm. Please <a href="{{ route('request.create') }}" class="text-indigo-600 hover:text-indigo-900">go back and submit the form</a>.</p>
                    @else
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Review Your Submission Details:</h3>
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Please check the information below. If correct, click "Confirm & Submit". To make changes, click "Back to Edit".</p>

                        {{-- Display Form Data --}}
                        <div class="space-y-4 mb-6 p-4 border border-gray-300 dark:border-gray-700 rounded-md">
                            <div>
                                <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Form Type:</strong>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"><span class="highlight-field-display">{{ $formData['request_type'] ?? 'N/A' }}</span></p>
                            </div>

                            @if($formData['request_type'] == 'IOM')
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 pt-2 border-t mt-4">IOM Details</h4>
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">From:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $fromDepartmentName }} ({{ $user->username }})</p>
                                </div>
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Department:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"><span class="highlight-field-display">{{ $departments[$formData['iom_to_department_id']]->dept_name ?? 'N/A' }}</span></p>
                                </div>
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Re (Subject):</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $formData['iom_re'] ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Priority:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $formData['iom_priority'] ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purpose:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        @php
                                            $purpose = $formData['iom_purpose'] ?? 'N/A';
                                            if ($purpose === 'Request' && !empty($formData['iom_specific_request_type'])) {
                                                $purpose .= ' - ' . $formData['iom_specific_request_type'];
                                            } elseif ($purpose === 'Others' && !empty($formData['iom_other_purpose'])) {
                                                $purpose .= ': ' . $formData['iom_other_purpose'];
                                            }
                                        @endphp
                                        {{ $purpose }}
                                    </p>
                                </div>
                                @if(!empty($formData['iom_date_needed']))
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Needed:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($formData['iom_date_needed'])->format('F j, Y') }}</p>
                                </div>
                                @endif
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description/Body:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $formData['iom_description'] ?? 'N/A' }}</p>
                                </div>

                            @elseif($formData['request_type'] == 'Leave')
                                <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 pt-2 border-t mt-4">Leave Details</h4>
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type of Leave:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($formData['leave_type'] ?? 'N/A') }}</p>
                                </div>
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Leave:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($formData['date_of_leave'])->format('F j, Y') }}</p>
                                </div>
                                <div>
                                    <strong class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description / Reason:</strong>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $formData['leave_description'] ?? 'N/A' }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Form to re-submit data --}}
                        <form method="POST" action="{{ route('request.store') }}">
                            @csrf
                            {{-- Hidden fields to re-submit all data --}}
                            @foreach($formData as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $subKey => $subValue)
                                        <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach

                            <div class="flex items-center justify-end mt-6 border-t pt-6 dark:border-gray-700">
                                <a href="{{ route('request.edit_before_confirmation') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                    {{ __('Back to Edit') }}
                                </a>
                                <x-primary-button>
                                    {{ __('Confirm & Submit Request') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 