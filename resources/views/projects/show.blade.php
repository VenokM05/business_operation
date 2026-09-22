<x-app-layout>
    <x-slot name="title">{{ $project->name }}</x-slot>

    <div class="flex items-center justify-between mb-4">
        @if ($project->client)
            <a href="{{ route('clients.show', $project->client) }}" class="text-link text-sm">{{ $project->client->company_name }}</a>
        @else
            <span class="text-sm text-muted">Archived client</span>
        @endif
        <div class="flex items-center gap-2">
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Edit</a>
            @endcan
            @can('delete', $project)
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Archive this project?');">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-500">Archive</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Overview</h2>
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div><dt class="text-gray-400">Status</dt><dd class="text-gray-800">{{ $project->status?->label() }}</dd></div>
                    <div><dt class="text-gray-400">Priority</dt><dd class="text-gray-800">{{ $project->priority?->label() }}</dd></div>
                    <div><dt class="text-gray-400">Manager</dt><dd class="text-gray-800">{{ $project->manager?->name ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">Start</dt><dd class="text-gray-800">{{ $project->start_date?->format('M d, Y') ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">End</dt><dd class="text-gray-800">{{ $project->end_date?->format('M d, Y') ?: '—' }}</dd></div>
                    <div><dt class="text-gray-400">Budget</dt><dd class="text-gray-800">{{ $project->budget !== null ? $project->currency.' '.number_format((float) $project->budget, 2) : '—' }}</dd></div>
                </dl>
                @if ($project->description)
                    <p class="mt-4 text-sm text-gray-600 whitespace-pre-line">{{ $project->description }}</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Service Requests ({{ $project->serviceRequests->count() }})</h2>
                <ul class="space-y-2 text-sm">
                    @forelse ($project->serviceRequests as $request)
                        <li class="flex items-center justify-between">
                            <a href="{{ route('service-requests.show', $request) }}" class="text-indigo-600 hover:underline">{{ $request->request_number }} · {{ $request->title }}</a>
                            <span class="text-xs text-gray-500">{{ $request->status?->label() }}</span>
                        </li>
                    @empty
                        <li class="text-gray-400">No requests.</li>
                    @endforelse
                </ul>
            </div>
            <x-activity-history :logs="$logs" />
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Team</h2>
                @can('assignStaff', $project)
                    <form method="POST" action="{{ route('projects.staff', $project) }}" class="mb-5 space-y-4">
                        @csrf @method('PATCH')
                        <fieldset class="max-h-52 overflow-y-auto space-y-2">
                            <legend class="text-sm text-muted mb-3">Select staff assigned to this project</legend>
                            @foreach ($staff as $member)
                                <label class="flex items-center gap-2 text-sm text-ink">
                                    <input type="checkbox" name="staff_ids[]" value="{{ $member->id }}" @checked(in_array($member->id, old('staff_ids', $project->users->modelKeys()))) class="rounded border-line text-brand focus:ring-brand">
                                    {{ $member->name }}
                                </label>
                            @endforeach
                        </fieldset>
                        <button class="action">Save Team</button>
                    </form>
                @endcan
                <ul class="space-y-1 text-sm text-gray-600">
                    @forelse ($project->users as $member)
                        <li>{{ $member->name }} <span class="text-gray-400">({{ $member->role?->label() }})</span></li>
                    @empty
                        <li class="text-gray-400">No staff assigned.</li>
                    @endforelse
                </ul>
            </div>

            <x-task-list :tasks="$project->tasks" parent-type="project" :parent-id="$project->id" />
        </div>
    </div>
</x-app-layout>
