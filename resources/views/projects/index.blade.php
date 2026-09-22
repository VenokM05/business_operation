<x-app-layout>
    <x-slot name="title">Projects</x-slot>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $projects->total() }} projects</p>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-500">New Project</a>
        @endcan
    </div>

    <x-list-filters resource="projects" :statuses="App\Enums\ProjectStatus::options()" :priorities="true" :clients="$clients" date-label="Start date" />

    <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3">Project</th>
                    <th class="px-4 py-3">Client</th>
                    <th class="px-4 py-3">Manager</th>
                    <th class="px-4 py-3">Priority</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-center">Requests</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse ($projects as $project)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3"><x-record-link :record="$project" resource="projects" :label="$project->name" /></td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->client?->company_name ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->manager?->name ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->priority?->label() }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$project->status" /></td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $project->service_requests_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No projects found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
</x-app-layout>
