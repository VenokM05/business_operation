<x-app-layout>
    <x-slot name="title">{{ $task->exists ? 'Edit Task' : 'New Task' }}</x-slot>
    <div class="panel max-w-3xl">
        <p class="text-sm text-muted mb-6">{{ $task->exists ? 'Update the work details. The parent stays fixed to preserve its history.' : 'Break project or request work into a clear, trackable task.' }}</p>
        <form method="POST" action="{{ $task->exists ? route('tasks.update', $task) : route('tasks.store') }}" class="space-y-5">
            @csrf
            @if ($task->exists) @method('PUT') @endif
            <div><label for="title" class="field-label">Title *</label><input id="title" name="title" class="field" value="{{ old('title', $task->title) }}" required maxlength="255"></div>
            @unless ($task->exists)
                <div class="grid sm:grid-cols-2 gap-4" x-data="{ type: @js(old('parent_type', request('parent_type', 'project'))) }">
                    <div>
                        <label for="parent_type" class="field-label">Parent type *</label>
                        <select id="parent_type" name="parent_type" class="field" x-model="type" required><option value="project">Project</option><option value="request">Service request</option></select>
                    </div>
                    <div x-show="type === 'project'">
                        <label for="parent-project" class="field-label">Project *</label>
                        <select id="parent-project" name="parent_id" class="field" :disabled="type !== 'project'" :required="type === 'project'">
                            <option value="">Select project</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('parent_id', request('parent_id')) == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="type === 'request'" x-cloak>
                        <label for="parent-request" class="field-label">Service request *</label>
                        <select id="parent-request" name="parent_id" class="field" :disabled="type !== 'request'" :required="type === 'request'">
                            <option value="">Select request</option>
                            @foreach ($requests as $request)
                                <option value="{{ $request->id }}" @selected(old('parent_id', request('parent_id')) == $request->id)>{{ $request->request_number }} · {{ $request->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endunless
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="field-label">Status *</label>
                    <select id="status" name="status" class="field" required>
                        @foreach (App\Enums\TaskStatus::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $task->status?->value) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="priority" class="field-label">Priority *</label>
                    <select id="priority" name="priority" class="field" required>
                        @foreach (App\Enums\Priority::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', $task->priority?->value) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label for="due_date" class="field-label">Due date</label><input id="due_date" name="due_date" type="date" class="field" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"></div>
                @unless (auth()->user()->isStaff())
                    <div>
                        <label for="assigned_to" class="field-label">Assigned staff</label>
                        <select id="assigned_to" name="assigned_to" class="field">
                            <option value="">Unassigned</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->id }}" @selected(old('assigned_to', $task->assigned_to) == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <p class="text-sm text-muted self-end pb-2">{{ $task->exists ? 'Only managers can reassign tasks.' : 'This task will be assigned to you.' }}</p>
                @endunless
            </div>
            <div><label for="description" class="field-label">Description</label><textarea id="description" name="description" rows="4" maxlength="5000" class="field">{{ old('description', $task->description) }}</textarea></div>
            <div class="flex gap-3"><button class="action">{{ $task->exists ? 'Save Task' : 'Create Task' }}</button><a href="{{ $task->exists ? route('tasks.show', $task) : route('tasks.index') }}" class="action-secondary">Cancel</a></div>
        </form>
    </div>
</x-app-layout>
