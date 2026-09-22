<x-app-layout>
    <x-slot name="title">Projects</x-slot>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $projects->total() }} projects</p>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-500">New Project</a>
        @endcan
    </div>

    <form method="GET" action="{{ route('projects.index') }}" class="bg-white rounded-lg shadow-sm p-4 mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search projects" class="rounded-md border-gray-300 text-sm">
        <select name="status" class="rounded-md border-gray-300 text-sm">
            <option value="">All statuses</option>
            @foreach (App\Enums\ProjectStatus::options() as $value => $label)
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
            <a href="{{ route('projects.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Reset</a>
        </div>
    </form>

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
                        <td class="px-4 py-3"><a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:underline">{{ $project->name }}</a></td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->client?->company_name ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->manager?->name ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $project->priority?->label() }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ $project->status?->label() }}</span></td>
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
