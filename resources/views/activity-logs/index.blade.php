<x-app-layout>
    <x-slot name="title">Activity Logs</x-slot>
    <div class="mb-5"><h2 class="section-title">Operational history</h2><p class="text-sm text-muted mt-1">{{ $logs->total() }} entries · Read-only audit trail of important actions</p></div>
    <form method="GET" action="{{ route('activity-logs.index') }}" class="panel grid sm:grid-cols-2 xl:grid-cols-4 gap-4 items-end mb-5">
        <div><label for="q" class="field-label">Search</label><input id="q" name="q" type="search" class="field" value="{{ request('q') }}" placeholder="Search descriptions" maxlength="255"></div>
        <div><label for="user_id" class="field-label">Actor</label><select id="user_id" name="user_id" class="field"><option value="">All actors</option>@foreach ($actors as $actor)<option value="{{ $actor->id }}" @selected(request('user_id') == $actor->id)>{{ $actor->name }}</option>@endforeach</select></div>
        <div><label for="action" class="field-label">Action</label><select id="action" name="action" class="field"><option value="">All actions</option>@foreach ($actions as $action)<option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>@endforeach</select></div>
        <div><label for="entity" class="field-label">Resource</label><select id="entity" name="entity" class="field"><option value="">All resources</option>@foreach (['client' => 'Clients', 'project' => 'Projects', 'request' => 'Service requests', 'task' => 'Tasks'] as $value => $label)<option value="{{ $value }}" @selected(request('entity') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div><label for="from" class="field-label">Recorded from</label><input id="from" name="from" type="date" value="{{ request('from') }}" class="field"></div>
        <div><label for="to" class="field-label">Recorded to</label><input id="to" name="to" type="date" value="{{ request('to') }}" class="field"></div>
        <div class="flex items-center gap-3"><button class="action">Filter</button><a href="{{ route('activity-logs.index') }}" class="text-link text-sm">Reset</a></div>
    </form>
    <div class="table-card">
        <table class="data-table">
            <thead><tr><th>When (UTC)</th><th>Actor</th><th>Action / resource</th><th>Description</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap">{{ $log->created_at?->format('M d, Y') }}<div class="text-xs text-muted mt-1">{{ $log->created_at?->format('H:i:s') }}</div></td>
                        <td class="whitespace-nowrap">{{ $log->user?->name ?? 'System / deleted user' }}</td>
                        <td><span class="badge-neutral">{{ $log->action }}</span><div class="text-xs text-muted mt-1">{{ $log->entity_type ? class_basename($log->entity_type).' #'.$log->entity_id : 'System' }}</div></td>
                        <td class="min-w-64 max-w-xl break-words">{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state">No activity matches these filters.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $logs->links() }}</div>
</x-app-layout>
