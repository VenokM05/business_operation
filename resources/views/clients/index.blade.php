<x-app-layout>
    <x-slot name="title">Clients</x-slot>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $clients->total() }} clients</p>
        @can('create', App\Models\Client::class)
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-500">
                New Client
            </a>
        @endcan
    </div>

    {{-- Filters --}}
    <x-list-filters resource="clients" :statuses="App\Enums\ClientStatus::options()" :industries="$industries" />

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3">Company</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Industry</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-center">Projects</th>
                    <th class="px-4 py-3 text-center">Requests</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse ($clients as $client)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <x-record-link :record="$client" resource="clients" :label="$client->company_name" />
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $client->contact_person ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $client->industry ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs
                                {{ $client->status === App\Enums\ClientStatus::Active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $client->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $client->projects_count }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $client->service_requests_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No clients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</x-app-layout>
