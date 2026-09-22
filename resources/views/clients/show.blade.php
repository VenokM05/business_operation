<x-app-layout>
    <x-slot name="title">{{ $client->company_name }}</x-slot>

    <div class="mb-4 flex items-center justify-between">
        <div class="text-sm text-muted">Client #{{ $client->id }}</div>
        <div class="flex items-center gap-2">
            @can('update', $client)
                <a href="{{ route('clients.edit', $client) }}" class="action-secondary">Edit</a>
            @endcan
            @can('delete', $client)
                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Archive this client?');">
                    @csrf @method('DELETE')
                    <button class="action-danger">Archive</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Company information --}}
        <div class="panel">
            <h2 class="section-title mb-4">Company Information</h2>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-muted">Contact</dt><dd class="font-medium text-ink">{{ $client->contact_person ?: '—' }}</dd></div>
                <div><dt class="text-muted">Email</dt><dd class="font-medium text-ink">{{ $client->email ?: '—' }}</dd></div>
                <div><dt class="text-muted">Phone</dt><dd class="font-medium text-ink">{{ $client->phone ?: '—' }}</dd></div>
                <div><dt class="text-muted">Industry</dt><dd class="font-medium text-ink">{{ $client->industry ?: '—' }}</dd></div>
                <div><dt class="text-muted">Address</dt><dd class="whitespace-pre-line font-medium text-ink">{{ $client->address ?: '—' }}</dd></div>
                <div><dt class="text-muted">Status</dt><dd class="mt-1">
                    <span class="badge {{ $client->status === App\Enums\ClientStatus::Active ? 'badge-success' : 'badge-neutral' }}">{{ $client->status->label() }}</span>
                </dd></div>
            </dl>
        </div>

        {{-- Projects --}}
        <div class="panel">
            <h2 class="section-title mb-4">Projects ({{ $client->projects->count() }})</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($client->projects as $project)
                    <li class="flex items-center justify-between gap-2"><a href="{{ route('projects.show', $project) }}" class="text-link">{{ $project->name }}</a>
                        <x-status-badge :status="$project->status" /></li>
                @empty
                    <li class="text-muted">No projects.</li>
                @endforelse
            </ul>
        </div>

        {{-- Service requests --}}
        <div class="panel">
            <h2 class="section-title mb-4">Service Requests ({{ $client->serviceRequests->count() }})</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($client->serviceRequests as $request)
                    <li class="flex items-center justify-between gap-2"><a href="{{ route('service-requests.show', $request) }}" class="text-link">{{ $request->request_number }}</a>
                        <x-status-badge :status="$request->status" /></li>
                @empty
                    <li class="text-muted">No requests.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <x-activity-history :logs="$logs" />
</x-app-layout>
