@props(['task'])
@if ($task->taskable && auth()->user()->can('view', $task->taskable))
    @if ($task->taskable instanceof App\Models\Project)
        <a class="text-link" href="{{ route('projects.show', $task->taskable) }}">{{ $task->taskable->name }}</a>
    @else
        <a class="text-link" href="{{ route('service-requests.show', $task->taskable) }}">{{ $task->taskable->request_number }}</a>
    @endif
@else
    <span class="text-muted">Parent unavailable</span>
@endif
