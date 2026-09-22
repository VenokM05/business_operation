<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    @if (auth()->user()->isStaff())
        <p class="text-sm text-muted mb-5">Your workspace · Statistics cover only records related to your assignments.</p>
    @endif
    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-5">
            <div class="text-sm text-gray-500">Clients</div>
            <div class="mt-1 text-3xl font-semibold text-gray-800">{{ number_format($stats['clients']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5">
            <div class="text-sm text-gray-500">Active Projects</div>
            <div class="mt-1 text-3xl font-semibold text-gray-800">{{ number_format($stats['active_projects']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5">
            <div class="text-sm text-gray-500">Open Requests</div>
            <div class="mt-1 text-3xl font-semibold text-indigo-600">{{ number_format($stats['open_requests']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-5">
            <div class="text-sm text-gray-500">Pending Requests</div>
            <div class="mt-1 text-3xl font-semibold text-amber-600">{{ number_format($stats['pending_requests']) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        {{-- Status chart --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Service Requests by Status</h2>
            <div class="space-y-3">
                @foreach ($chart as $row)
                    <div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>{{ $row['label'] }}</span>
                            <span>{{ $row['value'] }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded h-2">
                            <div class="bg-indigo-500 h-2 rounded"
                                 style="width: {{ $maxChart ? round(($row['value'] / $maxChart) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            @can('viewAny', App\Models\ActivityLog::class)
            <div class="flex justify-between items-center mb-4"><h2 class="section-title">Recent Activity</h2><a href="{{ route('activity-logs.index') }}" class="text-link text-sm">View all</a></div>
            <ul class="space-y-3">
                @forelse ($recentActivity as $log)
                    <li class="text-sm text-gray-600 flex items-start gap-2">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                        <span>
                            {{ $log->description }}
                            <span class="text-gray-400">· {{ $log->created_at?->diffForHumans() }}</span>
                        </span>
                    </li>
                @empty
                    <li class="text-sm text-gray-400">No activity yet.</li>
                @endforelse
            </ul>
            @else
                <h2 class="section-title mb-4">My Open Tasks</h2>
                <ul class="divide-y divide-line">
                    @forelse ($myTasks as $task)
                        <li class="py-3 flex justify-between gap-3 text-sm"><a href="{{ route('tasks.show', $task) }}" class="text-link">{{ $task->title }}</a><x-status-badge :status="$task->status" /></li>
                    @empty
                        <li class="text-sm text-muted">No open tasks assigned to you.</li>
                    @endforelse
                </ul>
                <a href="{{ route('tasks.index') }}" class="text-link text-sm inline-block mt-4">View tasks →</a>
            @endcan
        </div>
    </div>
</x-app-layout>
