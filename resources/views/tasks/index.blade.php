<x-app-layout>
    <x-slot name="title">Tasks</x-slot>
    <div class="flex items-center justify-between gap-4 mb-5">
        <div><h2 class="section-title">Keep work moving</h2><p class="text-sm text-muted mt-1">{{ $tasks->total() }} tasks · {{ auth()->user()->isStaff() ? 'Your assigned and created work' : 'Across projects and service requests' }}</p></div>
        <a href="{{ route('tasks.create') }}" class="action shrink-0">New Task</a>
    </div>
    <x-list-filters resource="tasks" :statuses="App\Enums\TaskStatus::options()" :priorities="true" :staff="$staff" date-label="Due date" />
    <div class="rounded-lg border border-line bg-surface overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Task</th><th>Parent</th><th>Assigned staff</th><th>Priority</th><th>Status</th><th>Due date</th></tr></thead>
            <tbody>
                @forelse ($tasks as $task)
                    <tr>
                        <td><x-record-link :record="$task" resource="tasks" :label="$task->title" /></td>
                        <td><x-task-parent :task="$task" /></td>
                        <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                        <td><x-status-badge :status="$task->priority" /></td>
                        <td><x-status-badge :status="$task->status" /></td>
                        <td class="whitespace-nowrap">{{ $task->due_date?->format('M d, Y') ?? '—' }}
                            @if ($task->due_date?->lt(today()) && ! $task->isCompleted()) <span class="block text-xs text-danger">Overdue</span> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="empty-state">No tasks match these filters. Reset the filters or create a task.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $tasks->links() }}</div>
</x-app-layout>
