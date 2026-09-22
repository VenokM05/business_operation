<x-app-layout>
    <x-slot name="title">Clients</x-slot>

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-muted">{{ $clients->total() }} clients</p>
        @can('create', App\Models\Client::class)
            <a href="{{ route('clients.create') }}" class="action">New Client</a>
        @endcan
    </div>

    {{-- Filters --}}
    <x-list-filters resource="clients" :statuses="App\Enums\ClientStatus::options()" :industries="$industries" />

    {{-- Table --}}
    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Industry</th>
                    <th>Status</th>
                    <th class="text-center">Projects</th>
                    <th class="text-center">Requests</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td><x-record-link :record="$client" resource="clients" :label="$client->company_name" /></td>
                        <td>{{ $client->contact_person ?: '—' }}</td>
                        <td>{{ $client->industry ?: '—' }}</td>
                        <td><x-status-badge :status="$client->status" /></td>
                        <td class="text-center tabular-nums">{{ $client->projects_count }}</td>
                        <td class="text-center tabular-nums">{{ $client->service_requests_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No clients found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</x-app-layout>
