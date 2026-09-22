<x-app-layout>
    <x-slot name="title">Reports</x-slot>

    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-ink">Reports</h1>
            <p class="text-sm text-muted">Operational roll-ups across clients, projects and service requests. Export any report to CSV or Excel.</p>
        </div>

        {{-- Client Report --}}
        <section class="panel">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">Client Report</h2>
                <div class="flex gap-2">
                    <a href="{{ route('reports.export', ['report' => 'clients', 'format' => 'csv']) }}" class="action-secondary">CSV</a>
                    <a href="{{ route('reports.export', ['report' => 'clients', 'format' => 'xlsx']) }}" class="action-secondary">Excel</a>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div><div class="text-2xl font-semibold text-ink">{{ $clients['total'] }}</div><div class="text-sm text-muted">Total clients</div></div>
                <div><div class="text-2xl font-semibold text-success">{{ $clients['active'] }}</div><div class="text-sm text-muted">Active</div></div>
                <div><div class="text-2xl font-semibold text-ink">{{ $clients['inactive'] }}</div><div class="text-sm text-muted">Inactive</div></div>
                <div><div class="text-2xl font-semibold text-muted">{{ $clients['archived'] }}</div><div class="text-sm text-muted">Archived</div></div>
            </div>
        </section>

        {{-- Project Report --}}
        <section class="panel">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">Project Report</h2>
                <div class="flex gap-2">
                    <a href="{{ route('reports.export', ['report' => 'projects', 'format' => 'csv']) }}" class="action-secondary">CSV</a>
                    <a href="{{ route('reports.export', ['report' => 'projects', 'format' => 'xlsx']) }}" class="action-secondary">Excel</a>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div><div class="text-2xl font-semibold text-brand">{{ $projects['active'] }}</div><div class="text-sm text-muted">Active</div></div>
                <div><div class="text-2xl font-semibold text-success">{{ $projects['completed'] }}</div><div class="text-sm text-muted">Completed</div></div>
                <div><div class="text-2xl font-semibold text-danger">{{ $projects['cancelled'] }}</div><div class="text-sm text-muted">Cancelled</div></div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Status</th><th class="text-right">Projects</th></tr></thead>
                    <tbody>
                        @foreach ($projects['by_status'] as $row)
                            <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['total'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Service Request Report --}}
        <section class="panel">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">Service Request Report</h2>
                <div class="flex gap-2">
                    <a href="{{ route('reports.export', ['report' => 'service-requests', 'format' => 'csv']) }}" class="action-secondary">CSV</a>
                    <a href="{{ route('reports.export', ['report' => 'service-requests', 'format' => 'xlsx']) }}" class="action-secondary">Excel</a>
                </div>
            </div>
            <p class="text-sm text-muted mb-4">{{ $requests['total'] }} total requests · {{ $requests['unassigned'] }} unassigned</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="field-label">By status</h3>
                    <table class="data-table">
                        <tbody>
                            @foreach ($requests['by_status'] as $row)
                                <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['total'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 class="field-label">By priority</h3>
                    <table class="data-table">
                        <tbody>
                            @foreach ($requests['by_priority'] as $row)
                                <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['total'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>
                    <h3 class="field-label">By category</h3>
                    <table class="data-table">
                        <tbody>
                            @foreach ($requests['by_category'] as $row)
                                <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['total'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <h3 class="field-label mt-6">By staff</h3>
            @if (empty($requests['by_staff']))
                <p class="empty-state">No assigned requests yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>Staff member</th><th class="text-right">Assigned requests</th></tr></thead>
                        <tbody>
                            @foreach ($requests['by_staff'] as $row)
                                <tr><td>{{ $row['name'] }}</td><td class="text-right">{{ $row['total'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
