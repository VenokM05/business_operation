@props(['logs'])
@can('viewAny', App\Models\ActivityLog::class)
    <section class="panel mt-6">
        <h2 class="section-title mb-4">Activity History</h2>
        <ol class="divide-y divide-line">
            @forelse ($logs as $log)
                <li class="py-3 text-sm"><p class="text-ink break-words">{{ $log->description }}</p><p class="text-xs text-muted mt-1">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at?->format('M d, Y g:i A') }} · {{ $log->action }}</p></li>
            @empty
                <li class="text-muted text-sm py-4">No activity recorded yet.</li>
            @endforelse
        </ol>
        <div class="mt-4">{{ $logs->links() }}</div>
    </section>
@endcan
