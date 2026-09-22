<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProjectStatus;
use App\Enums\RequestStatus;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends ApiController
{
    /** Role-aware summary statistics (PRD Section 4), mirroring the web dashboard. */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'clients' => Client::visibleTo($user)->count(),
            'active_projects' => Project::visibleTo($user)->where('status', ProjectStatus::Active->value)->count(),
            'open_requests' => ServiceRequest::visibleTo($user)->open()->count(),
            'completed_requests' => ServiceRequest::visibleTo($user)->whereIn('status', [
                RequestStatus::Resolved->value,
                RequestStatus::Closed->value,
            ])->count(),
            'pending_requests' => ServiceRequest::visibleTo($user)->where('status', RequestStatus::Pending->value)->count(),
        ];

        $byStatus = ServiceRequest::visibleTo($user)->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $requests_by_status = collect(RequestStatus::cases())->map(fn ($status) => [
            'status' => $status->value,
            'label' => $status->label(),
            'total' => (int) ($byStatus[$status->value] ?? 0),
        ])->all();

        $myTasks = Task::visibleTo($user)->where('assigned_to', $user->id)->open()->orderBy('due_date')->limit(10)->get()
            ->map(fn (Task $t) => ['id' => $t->id, 'title' => $t->title, 'status' => $t->status?->value, 'due_date' => $t->due_date?->toDateString()]);

        $recentActivity = $user->can('viewAny', ActivityLog::class)
            ? ActivityLog::with('user')->latest()->orderByDesc('id')->limit(10)->get()
                ->map(fn (ActivityLog $log) => ['id' => $log->id, 'action' => $log->action, 'description' => $log->description, 'user' => $log->user?->name, 'created_at' => $log->created_at?->toIso8601String()])
            : [];

        return $this->ok([
            'stats' => $stats,
            'requests_by_status' => $requests_by_status,
            'my_open_tasks' => $myTasks,
            'recent_activity' => $recentActivity,
        ], 'Dashboard statistics retrieved successfully.');
    }
}
