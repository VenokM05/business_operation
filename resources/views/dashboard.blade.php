<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight text-ink">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}</h2>
        @if (auth()->user()->isStaff())
            <p class="mt-1 text-sm text-muted">Your workspace &middot; statistics cover only records related to your assignments.</p>
        @else
            <p class="mt-1 text-sm text-muted">Here's what's happening across your operations today.</p>
        @endif
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card">
            <div>
                <div class="stat-label">Clients</div>
                <div class="stat-value">{{ number_format($stats['clients']) }}</div>
            </div>
            <span class="stat-icon bg-brand">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4M9 20H4v-2a4 4 0 014-4m5-3a4 4 0 100-8 4 4 0 000 8z"/></svg>
            </span>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Active Projects</div>
                <div class="stat-value">{{ number_format($stats['active_projects']) }}</div>
            </div>
            <span class="stat-icon bg-accent">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
            </span>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Open Requests</div>
                <div class="stat-value">{{ number_format($stats['open_requests']) }}</div>
            </div>
            <span class="stat-icon bg-brand-light">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </span>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Pending Requests</div>
                <div class="stat-value">{{ number_format($stats['pending_requests']) }}</div>
            </div>
            <span class="stat-icon bg-warning">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
            </span>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Status chart --}}
        <div class="panel">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="section-title">Service Requests by Status</h2>
                <a href="{{ route('service-requests.index') }}" class="text-link text-sm">View all</a>
            </div>
            <div class="space-y-4">
                @foreach ($chart as $row)
                    <div>
                        <div class="mb-1.5 flex justify-between text-xs">
                            <span class="font-medium text-ink">{{ $row['label'] }}</span>
                            <span class="tabular-nums text-muted">{{ $row['value'] }}</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-canvas">
                            <div class="h-2 rounded-full bg-gradient-to-r from-brand to-accent transition-all duration-500"
                                 style="width: {{ $maxChart ? round(($row['value'] / $maxChart) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent activity / My tasks --}}
        <div class="panel">
            @can('viewAny', App\Models\ActivityLog::class)
            <div class="mb-5 flex items-center justify-between"><h2 class="section-title">Recent Activity</h2><a href="{{ route('activity-logs.index') }}" class="text-link text-sm">View all</a></div>
            <ul class="space-y-4">
                @forelse ($recentActivity as $log)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-accent ring-4 ring-accent/15"></span>
                        <span class="text-ink">
                            {{ $log->description }}
                            <span class="block text-xs text-muted">{{ $log->created_at?->diffForHumans() }}</span>
                        </span>
                    </li>
                @empty
                    <li class="text-sm text-muted">No activity yet.</li>
                @endforelse
            </ul>
            @else
                <div class="mb-5 flex items-center justify-between"><h2 class="section-title">My Open Tasks</h2><a href="{{ route('tasks.index') }}" class="text-link text-sm">View all</a></div>
                <ul class="divide-y divide-line">
                    @forelse ($myTasks as $task)
                        <li class="flex items-center justify-between gap-3 py-3 text-sm"><a href="{{ route('tasks.show', $task) }}" class="text-link">{{ $task->title }}</a><x-status-badge :status="$task->status" /></li>
                    @empty
                        <li class="py-3 text-sm text-muted">No open tasks assigned to you.</li>
                    @endforelse
                </ul>
            @endcan
        </div>
    </div>
</x-app-layout>
