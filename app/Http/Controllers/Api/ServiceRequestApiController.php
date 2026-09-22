<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ServiceRequestRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Models\ActivityLog;
use App\Models\ServiceRequest;
use App\Services\RequestWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceRequestApiController extends ApiController
{
    public function __construct(private readonly RequestWorkflowService $workflow) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $requests = ServiceRequest::query()
            ->with(['client', 'assignee'])
            ->visibleTo($request->user())
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->priority($request->query('priority'))
            ->assignedTo($request->integer('assigned_to') ?: null)
            ->forClient($request->integer('client_id') ?: null)
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->query('category')))
            ->when($request->query('archive') === 'archived', fn ($q) => $q->onlyTrashed())
            ->latest()
            ->paginate($this->perPage());

        $requests->through(fn (ServiceRequest $r) => (new ServiceRequestResource($r))->resolve());

        return $this->paginated($requests, 'Service requests retrieved successfully.');
    }

    public function store(ServiceRequestRequest $request): JsonResponse
    {
        $serviceRequest = $this->workflow->create($request->safe()->only([
            'title', 'description', 'client_id', 'project_id', 'category', 'priority', 'due_date', 'assigned_to',
        ]), $request->user());
        $serviceRequest->load(['client', 'project', 'assignee']);

        return $this->ok((new ServiceRequestResource($serviceRequest))->resolve(), 'Service request created successfully.', 201);
    }

    public function show(ServiceRequest $service_request): JsonResponse
    {
        $this->authorize('view', $service_request);
        $service_request->load(['client', 'project', 'assignee']);

        return $this->ok((new ServiceRequestResource($service_request))->resolve(), 'Service request retrieved successfully.');
    }

    public function update(ServiceRequestRequest $request, ServiceRequest $service_request): JsonResponse
    {
        $updated = DB::transaction(function () use ($request, $service_request) {
            $current = ServiceRequest::lockForUpdate()->findOrFail($service_request->id);
            $this->authorize('update', $current);
            $current->update($request->safe()->only(['title', 'description', 'category', 'priority', 'due_date']));
            ActivityLog::record('request.updated', $current, "Updated request {$current->request_number}", $request->user()->id);

            return $current;
        });
        $updated->load(['client', 'project', 'assignee']);

        return $this->ok((new ServiceRequestResource($updated))->resolve(), 'Service request updated successfully.');
    }

    /** Archive (soft delete). */
    public function destroy(ServiceRequest $service_request): JsonResponse
    {
        $this->authorize('delete', $service_request);
        DB::transaction(function () use ($service_request) {
            $service_request->delete();
            ActivityLog::record('request.archived', $service_request, "Archived request {$service_request->request_number}");
        });

        return $this->ok(null, 'Service request archived successfully.');
    }
}
