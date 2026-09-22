<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\RequestStatus;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Role-aware dashboard (PRD Section 4).
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Base queries respect soft deletes automatically.
        $stats = [
            'clients' => Client::count(),
            'active_projects' => Project::where('status', ProjectStatus::Active->value)->count(),
            'open_requests' => ServiceRequest::open()->count(),
            'completed_requests' => ServiceRequest::whereIn('status', [
                RequestStatus::Resolved->value,
                RequestStatus::Closed->value,
            ])->count(),
            'pending_requests' => ServiceRequest::where('status', RequestStatus::Pending->value)->count(),
            'active_staff' => \App\Models\User::query()->count(),
        ];

        // Requests grouped by status for the bar chart.
        $byStatus = ServiceRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $chart = collect(RequestStatus::cases())->map(fn ($status) => [
            'label' => $status->label(),
            'value' => (int) ($byStatus[$status->value] ?? 0),
        ])->all();

        $maxChart = max(1, ...array_column($chart, 'value'));

        $recentActivity = ActivityLog::with('user')->latest()->limit(10)->get();

        return view('dashboard', compact('stats', 'chart', 'maxChart', 'recentActivity'));
    }
}
