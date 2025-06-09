<x-app-layout> {{-- Attempting to use common alias for layouts/app.blade.php --}}

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold mb-6">Admin Dashboard - All Document Requests</h1>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if($requests->isEmpty())
            <p class="text-gray-700">No document requests found.</p>
        @else
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <tr>
                            <th class="py-3 px-6 text-left">Request ID</th>
                            <th class="py-3 px-6 text-left">Form Type</th>
                            <th class="py-3 px-6 text-left">Title</th>
                            <th class="py-3 px-6 text-left">Requested By</th>
                            <th class="py-3 px-6 text-left">Requester's Dept.</th>
                            <th class="py-3 px-6 text-left">To Department</th>
                            <th class="py-3 px-6 text-center">Date Submitted</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        @foreach($requests as $request)
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left whitespace-nowrap">{{ $request->form_id }}</td>
                                <td class="py-3 px-6 text-left">{{ $request->form_type }}</td>
                                <td class="py-3 px-6 text-left">{{ $request->title ?? 'N/A' }}</td>
                                <td class="py-3 px-6 text-left">
                                    {{ $request->requester && $request->requester->employeeInfo ? $request->requester->employeeInfo->FirstName . ' ' . $request->requester->employeeInfo->LastName : 'N/A' }}
                                </td>
                                <td class="py-3 px-6 text-left">
                                    {{ $request->requester && $request->requester->department ? $request->requester->department->dept_name : 'N/A' }}
                                </td>
                                <td class="py-3 px-6 text-left">
                                    {{ $request->toDepartment ? $request->toDepartment->dept_name : 'N/A' }}
                                </td>
                                <td class="py-3 px-6 text-center">{{ $request->date_submitted ? $request->date_submitted->format('Y-m-d h:i A') : 'N/A' }}</td>
                                <td class="py-3 px-6 text-center">
                                    <span class="
                                        @if($request->status == 'Pending') bg-yellow-200 text-yellow-700
                                        @elseif($request->status == 'Approved') bg-green-200 text-green-700
                                        @elseif($request->status == 'Rejected') bg-red-200 text-red-700
                                        @elseif($request->status == 'Cancelled') bg-gray-200 text-gray-700
                                        @elseif($request->status == 'In Progress') bg-blue-200 text-blue-700
                                        @else bg-gray-300 text-gray-800
                                        @endif
                                        py-1 px-3 rounded-full text-xs">
                                        {{ $request->status ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    {{-- Add more actions like Edit/Delete if needed --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination (if you implement it in the controller) --}}
            {{-- <div class="mt-6">
                {{ $requests->links() }}
            </div> --}}
        @endif
    </div>

</x-app-layout>
