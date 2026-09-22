<x-app-layout>
    <x-slot name="title">Projects</x-slot>

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-muted">{{ $projects->total() }} projects</p>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="action">New Project</a>
        @endcan
    </div>

    <x-list-filters resource="projects" :statuses="App\Enums\ProjectStatus::options()" :priorities="true" :clients="$clients" date-label="Start date" />

    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Manager</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th class="text-center">Requests</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr>
                        <td><x-record-link :record="$project" resource="projects" :label="$project->name" /></td>
                        <td>{{ $project->client?->company_name ?: '—' }}</td>
                        <td>{{ $project->manager?->name ?: '—' }}</td>
                        <td><x-status-badge :status="$project->priority" /></td>
                        <td><x-status-badge :status="$project->status" /></td>
                        <td class="text-center tabular-nums">{{ $project->service_requests_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No projects found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $projects->links() }}</div>
</x-app-layout>
