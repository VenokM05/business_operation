<x-app-layout>
    <x-slot name="title">Service Requests</x-slot>

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-muted">{{ $requests->total() }} requests</p>
        <a href="{{ route('service-requests.create') }}" class="action">New Request</a>
    </div>

    <x-list-filters resource="service-requests" :statuses="App\Enums\RequestStatus::options()" :priorities="true" :clients="$clients" :staff="$staff" :categories="true" date-label="Due date" />

    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Client</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Assignee</th>
                    <th>Status</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr>
                        <td><x-record-link :record="$request" resource="service-requests" :label="$request->request_number" />
                            <div class="text-xs text-muted">{{ $request->title }}</div></td>
                        <td>{{ $request->client?->company_name ?: '—' }}</td>
                        <td>{{ $request->category?->label() }}</td>
                        <td><x-status-badge :status="$request->priority" /></td>
                        <td>{{ $request->assignee?->name ?: '—' }}</td>
                        <td><x-status-badge :status="$request->status" /></td>
                        <td class="whitespace-nowrap">{{ $request->due_date?->format('M d, Y') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">No requests found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
</x-app-layout>
