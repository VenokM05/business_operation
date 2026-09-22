<x-app-layout>
    <x-slot name="title">Task #{{ $task->id }}</x-slot>
    <div class="flex flex-wrap justify-between gap-3 mb-5">
        <a href="{{ route('tasks.index') }}" class="text-link text-sm">← All tasks</a>
        <div class="flex gap-2">
            @can('update', $task)<a href="{{ route('tasks.edit', $task) }}" class="action">Edit Task</a>@endcan
            @can('delete', $task)
                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Archive this task?');">@csrf @method('DELETE')<button class="action-danger">Archive</button></form>
            @endcan
        </div>
    </div>
    <article class="panel max-w-4xl">
        <div class="flex flex-wrap justify-between gap-4"><h2 class="text-xl font-semibold text-ink break-words">{{ $task->title }}</h2><x-status-badge :status="$task->status" /></div>
        <p class="mt-4 text-sm text-muted whitespace-pre-line break-words">{{ $task->description ?: 'No description provided.' }}</p>
        <dl class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8 text-sm">
            <div><dt class="text-muted mb-1">Parent</dt><dd><x-task-parent :task="$task" /></dd></div>
            <div><dt class="text-muted mb-1">Assigned staff</dt><dd>{{ $task->assignee?->name ?? 'Unassigned' }}</dd></div>
            <div><dt class="text-muted mb-1">Priority</dt><dd><x-status-badge :status="$task->priority" /></dd></div>
            <div><dt class="text-muted mb-1">Due date</dt><dd>{{ $task->due_date?->format('M d, Y') ?? 'Not set' }}</dd></div>
            <div><dt class="text-muted mb-1">Created by</dt><dd>{{ $task->creator?->name ?? 'System' }}</dd></div>
            <div><dt class="text-muted mb-1">Last updated</dt><dd>{{ $task->updated_at?->format('M d, Y g:i A') }}</dd></div>
        </dl>
    </article>
</x-app-layout>
