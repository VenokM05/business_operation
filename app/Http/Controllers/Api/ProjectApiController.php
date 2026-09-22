<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectApiController extends ApiController
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $projects = Project::query()
            ->with(['client', 'manager'])
            ->visibleTo($request->user())
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->priority($request->query('priority'))
            ->forClient($request->integer('client_id') ?: null)
            ->when($request->query('archive') === 'archived', fn ($q) => $q->onlyTrashed())
            ->latest()
            ->paginate($this->perPage());

        $projects->through(fn (Project $p) => (new ProjectResource($p))->resolve());

        return $this->paginated($projects, 'Projects retrieved successfully.');
    }

    public function store(ProjectRequest $request): JsonResponse
    {
        $project = $this->service->save(new Project, $request->validated(), $request->user());
        $project->load(['client', 'manager']);

        return $this->ok((new ProjectResource($project))->resolve(), 'Project created successfully.', 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);
        $project->load(['client', 'manager']);

        return $this->ok((new ProjectResource($project))->resolve(), 'Project retrieved successfully.');
    }

    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        $project = $this->service->save($project, $request->validated(), $request->user());
        $project->load(['client', 'manager']);

        return $this->ok((new ProjectResource($project))->resolve(), 'Project updated successfully.');
    }

    /** Archive (soft delete). */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();

        return $this->ok(null, 'Project archived successfully.');
    }
}
