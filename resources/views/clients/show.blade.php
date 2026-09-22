<x-app-layout>
    <x-slot name="title">{{ $client->company_name }}</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-500">Client #{{ $client->id }}</div>
        <div class="flex items-center gap-2">
            @can('update', $client)
                <a href="{{ route('clients.edit', $client) }}" class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Edit</a>
            @endcan
            @can('delete', $client)
                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Archive this client?');">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-500">Archive</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Company information --}}
        <div class="lg:col-span-1 bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Company Information</h2>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-gray-400">Contact</dt><dd class="text-gray-800">{{ $client->contact_person ?: '—' }}</dd></div>
                <div><dt class="text-gray-400">Email</dt><dd class="text-gray-800">{{ $client->email ?: '—' }}</dd></div>
                <div><dt class="text-gray-400">Phone</dt><dd class="text-gray-800">{{ $client->phone ?: '—' }}</dd></div>
                <div><dt class="text-gray-400">Industry</dt><dd class="text-gray-800">{{ $client->industry ?: '—' }}</dd></div>
                <div><dt class="text-gray-400">Address</dt><dd class="text-gray-800 whitespace-pre-line">{{ $client->address ?: '—' }}</dd></div>
                <div><dt class="text-gray-400">Status</dt><dd>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $client->status === App\Enums\ClientStatus::Active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $client->status->label() }}</span>
                </dd></div>
            </dl>
        </div>

        {{-- Projects --}}
        <div class="lg:col-span-1 bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Projects ({{ $client->projects->count() }})</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($client->projects as $project)
                    <li><a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:underline">{{ $project->name }}</a>
                        <span class="text-gray-400">· {{ $project->status?->label() }}</span></li>
                @empty
                    <li class="text-gray-400">No projects.</li>
                @endforelse
            </ul>
        </div>

        {{-- Service requests --}}
        <div class="lg:col-span-1 bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Service Requests ({{ $client->serviceRequests->count() }})</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($client->serviceRequests as $request)
                    <li><a href="{{ route('service-requests.show', $request) }}" class="text-indigo-600 hover:underline">{{ $request->request_number }}</a>
                        <span class="text-gray-600">· {{ $request->title }}</span></li>
                @empty
                    <li class="text-gray-400">No requests.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <x-activity-history :logs="$logs" />
</x-app-layout>
