@props(['tasks', 'parentType', 'parentId'])
<section class="panel">
    <div class="flex items-center justify-between gap-3 mb-4">
        <h2 class="section-title">Tasks ({{ $tasks->count() }})</h2>
        <a class="text-link text-sm" href="{{ route('tasks.create', ['parent_type' => $parentType, 'parent_id' => $parentId]) }}">Add Task</a>
    </div>
    <ul class="divide-y divide-line">
        @forelse ($tasks as $task)
            <li class="py-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                <div><a class="text-link" href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a><p class="text-muted text-xs mt-1">{{ $task->assignee?->name ?? 'Unassigned' }} · {{ $task->due_date?->format('M d') ?? 'No due date' }}</p></div>
                <x-status-badge :status="$task->status" />
            </li>
        @empty
            <li class="text-sm text-muted py-4">No visible tasks yet.</li>
        @endforelse
    </ul>
</section>
