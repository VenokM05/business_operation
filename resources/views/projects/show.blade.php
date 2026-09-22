<x-app-layout>
    <x-slot name="title">{{ $project->name }}</x-slot>

    <div class="mb-4 flex items-center justify-between">
        @if ($project->client)
            <a href="{{ route('clients.show', $project->client) }}" class="text-link text-sm">{{ $project->client->company_name }}</a>
        @else
            <span class="text-sm text-muted">Archived client</span>
        @endif
        <div class="flex items-center gap-2">
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}" class="action-secondary">Edit</a>
            @endcan
            @can('delete', $project)
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Archive this project?');">
                    @csrf @method('DELETE')
                    <button class="action-danger">Archive</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="panel">
                <h2 class="section-title mb-4">Overview</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-muted">Status</dt><dd class="font-medium text-ink">{{ $project->status?->label() }}</dd></div>
                    <div><dt class="text-muted">Priority</dt><dd class="font-medium text-ink">{{ $project->priority?->label() }}</dd></div>
                    <div><dt class="text-muted">Manager</dt><dd class="font-medium text-ink">{{ $project->manager?->name ?: '—' }}</dd></div>
                    <div><dt class="text-muted">Start</dt><dd class="font-medium text-ink">{{ $project->start_date?->format('M d, Y') ?: '—' }}</dd></div>
                    <div><dt class="text-muted">End</dt><dd class="font-medium text-ink">{{ $project->end_date?->format('M d, Y') ?: '—' }}</dd></div>
                    <div><dt class="text-muted">Budget</dt><dd class="font-medium text-ink">{{ $project->budget !== null ? $project->currency.' '.number_format((float) $project->budget, 2) : '—' }}</dd></div>
                </dl>
                @if ($project->description)
                    <p class="mt-4 whitespace-pre-line text-sm text-muted">{{ $project->description }}</p>
                @endif
            </div>

            <div class="panel">
                <h2 class="section-title mb-4">Service Requests ({{ $project->serviceRequests->count() }})</h2>
                <ul class="space-y-2 text-sm">
                    @forelse ($project->serviceRequests as $request)
                        <li class="flex items-center justify-between gap-2">
                            <a href="{{ route('service-requests.show', $request) }}" class="text-link">{{ $request->request_number }} · {{ $request->title }}</a>
                            <x-status-badge :status="$request->status" />
                        </li>
                    @empty
                        <li class="text-muted">No requests.</li>
                    @endforelse
                </ul>
            </div>
            <x-activity-history :logs="$logs" />
        </div>

        <div class="space-y-6">
            <div class="panel">
                <h2 class="section-title mb-4">Team</h2>
                @can('assignStaff', $project)
                    <form method="POST" action="{{ route('projects.staff', $project) }}" class="mb-5 space-y-4">
                        @csrf @method('PATCH')
                        <fieldset class="max-h-52 space-y-2 overflow-y-auto">
                            <legend class="mb-3 text-sm text-muted">Select staff assigned to this project</legend>
                            @foreach ($staff as $member)
                                <label class="flex items-center gap-2 text-sm text-ink">
                                    <input type="checkbox" name="staff_ids[]" value="{{ $member->id }}" @checked(in_array($member->id, old('staff_ids', $project->users->modelKeys()))) class="rounded border-line text-brand focus:ring-brand/30">
                                    {{ $member->name }}
                                </label>
                            @endforeach
                        </fieldset>
                        <button class="action">Save Team</button>
                    </form>
                @endcan
                <ul class="space-y-1 text-sm text-ink">
                    @forelse ($project->users as $member)
                        <li>{{ $member->name }} <span class="text-muted">({{ $member->role?->label() }})</span></li>
                    @empty
                        <li class="text-muted">No staff assigned.</li>
                    @endforelse
                </ul>
            </div>

            <x-task-list :tasks="$project->tasks" parent-type="project" :parent-id="$project->id" />
        </div>
    </div>
</x-app-layout>
