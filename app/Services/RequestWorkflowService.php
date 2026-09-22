<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\RequestUpdateType;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\RequestUpdate;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * Encapsulates the service-request workflow (PRD Section 8) so controllers stay thin.
 */
class RequestWorkflowService
{
    public function create(array $data, User $actor): ServiceRequest
    {
        Gate::forUser($actor)->authorize('create', ServiceRequest::class);

        return DB::transaction(function () use ($data, $actor) {
            $assignee = ! empty($data['assigned_to']) ? User::findOrFail($data['assigned_to']) : null;
            if (! empty($data['project_id'])) {
                $project = Project::lockForUpdate()->findOrFail($data['project_id']);
                Gate::forUser($actor)->authorize('view', $project);
                $data['client_id'] = $project->client_id;
            }
            $request = ServiceRequest::create([
                ...$data, 'assigned_to' => null, 'status' => RequestStatus::New, 'created_by' => $actor->id,
            ]);
            ActivityLog::record('request.created', $request, "Created request {$request->request_number}", $actor->id);
            if ($assignee) {
                $request = $this->assign($request, $assignee, $actor);
            }

            return $request;
        });
    }

    /**
     * Move a request to a new status, enforcing the allowed transition map.
     *
     * @throws ValidationException when the transition is not legal.
     */
    public function changeStatus(ServiceRequest $request, RequestStatus $to, User $actor): ServiceRequest
    {
        return DB::transaction(function () use ($request, $to, $actor) {
            $current = ServiceRequest::lockForUpdate()->findOrFail($request->id);
            Gate::forUser($actor)->authorize('changeStatus', $current);

            return $this->transition($current, $to, $actor);
        });
    }

    private function transition(ServiceRequest $request, RequestStatus $to, User $actor): ServiceRequest
    {
        $from = $request->status;

        if ($from === $to) {
            return $request;
        }

        if (! $from->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => "Illegal transition: {$from->label()} → {$to->label()}.",
            ]);
        }

        if (in_array($to, [RequestStatus::Assigned, RequestStatus::InProgress, RequestStatus::Pending], true)
            && ! $request->assigned_to) {
            throw ValidationException::withMessages(['status' => 'Assign a staff member before starting work.']);
        }

        $request->status = $to;
        $request->save();

        RequestUpdate::create([
            'service_request_id' => $request->id,
            'user_id' => $actor->id,
            'type' => RequestUpdateType::StatusChange,
            'message' => "changed status to {$to->label()}",
            'old_status' => $from->value,
            'new_status' => $to->value,
            'created_at' => now(),
        ]);

        ActivityLog::record(
            'request.status_changed',
            $request,
            "{$actor->name} changed {$request->request_number} from {$from->label()} to {$to->label()}",
            $actor->id,
        );

        return $request;
    }

    /**
     * Assign (or unassign) staff. Moves NEW → ASSIGNED automatically when relevant.
     */
    public function assign(ServiceRequest $request, ?User $assignee, User $actor): ServiceRequest
    {
        return DB::transaction(function () use ($request, $assignee, $actor) {
            $current = ServiceRequest::lockForUpdate()->findOrFail($request->id);
            Gate::forUser($actor)->authorize('assignStaff', $current);
            if ((int) $current->assigned_to === (int) $assignee?->id) {
                return $current;
            }
            if ($current->status->isTerminal()) {
                throw ValidationException::withMessages(['assigned_to' => 'Closed or cancelled requests cannot be reassigned.']);
            }
            if (! $assignee && in_array($current->status, [RequestStatus::Assigned, RequestStatus::InProgress, RequestStatus::Pending], true)) {
                throw ValidationException::withMessages(['assigned_to' => 'Active work must have an assignee. Reassign it to another staff member.']);
            }
            $previous = $current->assignee?->name ?? 'Unassigned';
            $current->update(['assigned_to' => $assignee?->id]);
            RequestUpdate::create([
                'service_request_id' => $current->id,
                'user_id' => $actor->id,
                'type' => RequestUpdateType::Assignment,
                'message' => "changed assignee from {$previous} to ".($assignee?->name ?? 'Unassigned'),
            ]);
            ActivityLog::record('request.assigned', $current,
                "{$actor->name} changed {$current->request_number} assignee from {$previous} to ".($assignee?->name ?? 'Unassigned'), $actor->id);

            if ($assignee && $current->status === RequestStatus::New) {
                $this->transition($current, RequestStatus::Assigned, $actor);
            }

            return $current;
        });
    }

    public function addComment(ServiceRequest $request, string $message, User $actor): RequestUpdate
    {
        return DB::transaction(function () use ($request, $message, $actor) {
            $current = ServiceRequest::lockForUpdate()->findOrFail($request->id);
            Gate::forUser($actor)->authorize('update', $current);
            $update = RequestUpdate::create([
                'service_request_id' => $current->id,
                'user_id' => $actor->id,
                'type' => RequestUpdateType::Comment,
                'message' => $message,
            ]);
            ActivityLog::record('request.commented', $current, "{$actor->name} commented on {$current->request_number}", $actor->id);

            return $update;
        });
    }
}
