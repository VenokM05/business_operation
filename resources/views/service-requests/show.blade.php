<x-app-layout>
    <x-slot name="title">{{ $request->request_number }}</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-muted">
            {{ $request->client?->company_name ?? 'Archived client' }}
            @if ($request->project && auth()->user()->can('view', $request->project))
                · <a href="{{ route('projects.show', $request->project) }}" class="text-link">{{ $request->project->name }}</a>
            @endif
        </div>
        <div class="flex gap-2">
        @can('update', $request)
            <a href="{{ route('service-requests.edit', $request) }}" class="action-secondary">Edit</a>
        @endcan
        @can('delete', $request)
            <form method="POST" action="{{ route('service-requests.destroy', $request) }}" onsubmit="return confirm('Archive this request?');">
                @csrf @method('DELETE')
                <button class="action-danger">Archive</button>
            </form>
        @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="panel">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">{{ $request->title }}</h2>
                        <p class="mt-2 whitespace-pre-line text-sm text-muted">{{ $request->description ?: 'No description provided.' }}</p>
                    </div>
                    <x-status-badge :status="$request->status" />
                </div>
                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                    <div><dt class="text-muted">Category</dt><dd class="font-medium text-ink">{{ $request->category?->label() }}</dd></div>
                    <div><dt class="text-muted">Priority</dt><dd class="font-medium text-ink">{{ $request->priority?->label() }}</dd></div>
                    <div><dt class="text-muted">Assignee</dt><dd class="font-medium text-ink">{{ $request->assignee?->name ?: 'Unassigned' }}</dd></div>
                    <div><dt class="text-muted">Due</dt><dd class="font-medium text-ink">{{ $request->due_date?->format('M d, Y') ?: '—' }}</dd></div>
                </dl>
            </div>

            {{-- Activity timeline (PRD Section 9) --}}
            <div class="panel">
                <h2 class="section-title mb-4">Activity</h2>
                <ol class="relative ml-3 space-y-5 border-l border-line">
                    @forelse ($updates as $update)
                        <li class="ml-5">
                            <span class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full bg-accent ring-4 ring-accent/15"></span>
                            <div class="text-sm text-ink">
                                <span class="font-medium">{{ $update->user?->name ?: 'System' }}</span>
                                <span class="whitespace-pre-line break-words">{{ $update->message }}</span>
                            </div>
                            <div class="text-xs text-muted mt-1">{{ $update->type->label() }}
                                @if ($update->old_status && $update->new_status)
                                    · {{ App\Enums\RequestStatus::from($update->old_status)->label() }} → {{ App\Enums\RequestStatus::from($update->new_status)->label() }}
                                @endif
                            </div>
                            <div class="mt-1 text-xs text-muted">{{ $update->created_at?->format('M d, Y g:i A') }}</div>
                        </li>
                    @empty
                        <li class="text-sm text-muted">No activity yet.</li>
                    @endforelse
                </ol>
                <div class="mt-5">{{ $updates->links() }}</div>

                @can('update', $request)
                    <form method="POST" action="{{ route('service-requests.comments', $request) }}" class="mt-6 space-y-3">
                        @csrf
                        <label for="message" class="field-label">Progress note</label>
                        <textarea id="message" name="message" placeholder="Add a progress note…" rows="3" class="field" required maxlength="2000">{{ old('message') }}</textarea>
                        <button class="action">Add Comment</button>
                    </form>
                @endcan
            </div>
            <x-task-list :tasks="$request->tasks" parent-type="request" :parent-id="$request->id" />
        </div>

        {{-- Sidebar actions --}}
        <div class="space-y-6">
            {{-- Status transition --}}
            <div class="panel">
                <h2 class="section-title mb-3">Change Status</h2>
                @can('changeStatus', $request)
                    @if ($allowedTransitions->isEmpty())
                        <p class="text-sm text-muted">No further transitions ({{ $request->status?->label() }} is terminal).</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($allowedTransitions as $to)
                                <form method="POST" action="{{ route('service-requests.status', $request) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $to->value }}">
                                    <button class="action">{{ $to->label() }}</button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p class="text-sm text-muted">You don't have permission to change status.</p>
                @endcan
                @error('status')<p class="mt-2 text-xs text-danger">{{ $message }}</p>@enderror
            </div>

            {{-- Assignment --}}
            <div class="panel">
                <h2 class="section-title mb-3">Assigned Staff</h2>
                @can('assignStaff', $request)
                    @if (! $request->status->isTerminal())
                    <form method="POST" action="{{ route('service-requests.assign', $request) }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <label for="assigned_to" class="sr-only">Assigned staff</label>
                        <select id="assigned_to" name="assigned_to" class="field">
                            <option value="">— Unassigned —</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->id }}" @selected($request->assigned_to == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <x-primary-button>Save</x-primary-button>
                    </form>
                    <p class="text-xs text-muted mt-3">Assigning a new request starts the workflow. Active work must retain an assignee.</p>
                    @else
                        <p class="text-sm text-muted">{{ $request->assignee?->name ?? 'Unassigned' }} · Assignment is locked for terminal requests.</p>
                    @endif
                @else
                    <p class="text-sm text-ink">{{ $request->assignee?->name ?: 'Unassigned' }}</p>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
