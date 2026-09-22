<?php

namespace App\Services;

use App\Enums\ClientStatus;
use App\Enums\Priority;
use App\Enums\ProjectStatus;
use App\Enums\RequestCategory;
use App\Enums\RequestStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Aggregations for the Reports section (PRD Section 12).
 *
 * Reports are Admin/Manager only, but every query still runs through the same
 * visibleTo() scopes used across the app so the numbers can never leak records
 * a viewer is not allowed to see.
 */
class ReportService
{
    public function clientReport(User $user): array
    {
        $base = fn () => Client::visibleTo($user);

        return [
            'total' => $base()->count(),
            'active' => $base()->where('status', ClientStatus::Active->value)->count(),
            'inactive' => $base()->where('status', ClientStatus::Inactive->value)->count(),
            'archived' => Client::visibleTo($user)->onlyTrashed()->count(),
        ];
    }

    public function projectReport(User $user): array
    {
        $counts = Project::visibleTo($user)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => Project::visibleTo($user)->count(),
            'by_status' => collect(ProjectStatus::cases())->map(fn ($s) => [
                'status' => $s->value,
                'label' => $s->label(),
                'total' => (int) ($counts[$s->value] ?? 0),
            ])->values()->all(),
            // Convenience figures explicitly called out in the PRD.
            'active' => (int) ($counts[ProjectStatus::Active->value] ?? 0),
            'completed' => (int) ($counts[ProjectStatus::Completed->value] ?? 0),
            'cancelled' => (int) ($counts[ProjectStatus::Cancelled->value] ?? 0),
        ];
    }

    public function serviceRequestReport(User $user): array
    {
        // pluck() on the Builder (not a hydrated collection) keeps the group keys
        // as raw strings, so they line up with the enum ->value lookups below.
        $byStatus = $this->enumBreakdown(
            ServiceRequest::visibleTo($user)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            RequestStatus::cases(),
        );
        $byPriority = $this->enumBreakdown(
            ServiceRequest::visibleTo($user)->selectRaw('priority, COUNT(*) as total')->groupBy('priority')->pluck('total', 'priority'),
            Priority::cases(),
        );
        $byCategory = $this->enumBreakdown(
            ServiceRequest::visibleTo($user)->selectRaw('category, COUNT(*) as total')->groupBy('category')->pluck('total', 'category'),
            RequestCategory::cases(),
        );

        $byStaff = ServiceRequest::visibleTo($user)
            ->selectRaw('service_requests.assigned_to, users.name, COUNT(*) as total')
            ->join('users', 'users.id', '=', 'service_requests.assigned_to')
            ->groupBy('service_requests.assigned_to', 'users.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'total' => (int) $row->total])
            ->all();

        $unassigned = ServiceRequest::visibleTo($user)->whereNull('assigned_to')->count();

        return [
            'total' => ServiceRequest::visibleTo($user)->count(),
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'by_category' => $byCategory,
            'by_staff' => $byStaff,
            'unassigned' => $unassigned,
        ];
    }

    /**
     * Normalise a raw value=>count map into a stable, fully-labelled list.
     *
     * @param  Collection<string,int>  $counts
     * @param  array<int, RequestStatus|Priority|RequestCategory|ProjectStatus>  $cases
     * @return array<int, array{value:string,label:string,total:int}>
     */
    private function enumBreakdown($counts, array $cases): array
    {
        return collect($cases)->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
            'total' => (int) ($counts[$case->value] ?? 0),
        ])->all();
    }
}
