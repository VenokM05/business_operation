<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\RequestUpdateType;
use App\Models\ActivityLog;
use App\Models\RequestUpdate;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Encapsulates the service-request workflow (PRD Section 8) so controllers stay thin.
 */
class RequestWorkflowService
{
    /**
     * Move a request to a new status, enforcing the allowed transition map.
     *
     * @throws ValidationException when the transition is not legal.
     */
    public function changeStatus(ServiceRequest $request, RequestStatus $to, User $actor): ServiceRequest
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
        $request->assigned_to = $assignee?->id;
        $request->save();

        if ($assignee && $request->status === RequestStatus::New) {
            // Legal transition New → Assigned.
            $this->changeStatus($request, RequestStatus::Assigned, $actor);
        }

        RequestUpdate::create([
            'service_request_id' => $request->id,
            'user_id' => $actor->id,
            'type' => RequestUpdateType::Assignment,
            'message' => $assignee
                ? "assigned the request to {$assignee->name}"
                : 'removed the assignee',
            'created_at' => now(),
        ]);

        ActivityLog::record(
            'request.assigned',
            $request,
            $assignee
                ? "{$actor->name} assigned {$request->request_number} to {$assignee->name}"
                : "{$actor->name} unassigned {$request->request_number}",
            $actor->id,
        );

        return $request;
    }

    public function addComment(ServiceRequest $request, string $message, User $actor): RequestUpdate
    {
        $update = RequestUpdate::create([
            'service_request_id' => $request->id,
            'user_id' => $actor->id,
            'type' => RequestUpdateType::Comment,
            'message' => $message,
            'created_at' => now(),
        ]);

        ActivityLog::record('request.commented', $request, "{$actor->name} commented on {$request->request_number}", $actor->id);

        return $update;
    }
}
