<x-app-layout>
    <x-slot name="title">Service Requests</x-slot>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $requests->total() }} requests</p>
        <a href="{{ route('service-requests.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-500">New Request</a>
    </div>

    <form method="GET" action="{{ route('service-requests.index') }}" class="bg-white rounded-lg shadow-sm p-4 mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search number / title" class="rounded-md border-gray-300 text-sm">
        <select name="status" class="rounded-md border-gray-300 text-sm">
            <option value="">All statuses</option>
            @foreach (App\Enums\RequestStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="priority" class="rounded-md border-gray-300 text-sm">
            <option value="">All priorities</option>
            @foreach (App\Enums\Priority::options() as $value => $label)
                <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">Filter</button>
            <a href="{{ route('service-requests.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3">Request</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Priority</th>
                    <th class="px-4 py-3">Assignee</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse ($requests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><a href="{{ route('service-requests.show', $request) }}" class="font-medium text-indigo-600 hover:underline">{{ $request->request_number }}</a>
                            <div class="text-gray-500 text-xs">{{ $request->title }}</div></td>
                        <td class="px-4 py-3 text-gray-600">{{ $request->client?->company_name ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $request->category?->label() }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $request->priority?->label() }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $request->assignee?->name ?: '—' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $request->status?->label() }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $request->due_date?->format('M d, Y') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
</x-app-layout>
