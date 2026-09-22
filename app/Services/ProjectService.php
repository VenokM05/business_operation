<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProjectService
{
    public function save(Project $project, array $data, User $actor): Project
    {
        Gate::forUser($actor)->authorize($project->exists ? 'update' : 'create', $project->exists ? $project : Project::class);

        return DB::transaction(function () use ($project, $data, $actor) {
            $creating = ! $project->exists;
            if (! $creating) {
                $project = Project::lockForUpdate()->findOrFail($project->id);
            }
            $oldStatus = $project->status;
            $oldManager = $project->project_manager_id;
            $project->fill($data)->save();
            ActivityLog::record($creating ? 'project.created' : 'project.updated', $project,
                ($creating ? 'Created project ' : 'Updated project ').$project->name, $actor->id);
            if (! $creating && $oldStatus !== $project->status) {
                ActivityLog::record('project.status_changed', $project,
                    "Changed {$project->name} from {$oldStatus->label()} to {$project->status->label()}", $actor->id);
            }
            if ((int) $oldManager !== (int) $project->project_manager_id) {
                ActivityLog::record('project.manager_assigned', $project,
                    "Changed {$project->name} manager to ".($project->manager?->name ?? 'Unassigned'), $actor->id);
            }

            return $project;
        });
    }

    public function assignTeam(Project $project, array $ids, User $actor): void
    {
        Gate::forUser($actor)->authorize('assignStaff', $project);
        DB::transaction(function () use ($project, $ids, $actor) {
            $project = Project::lockForUpdate()->findOrFail($project->id);
            $before = $project->users()->orderBy('name')->pluck('name')->implode(', ') ?: 'No staff';
            $changes = $project->users()->sync($ids);
            if ($changes['attached'] || $changes['detached']) {
                $after = $project->users()->orderBy('name')->pluck('name')->implode(', ') ?: 'No staff';
                ActivityLog::record('project.staff_assigned', $project,
                    "Changed {$project->name} team from [{$before}] to [{$after}]", $actor->id);
            }
        });
    }
}
