<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Http\Requests\ProjectRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();

        $projects = Project::query()
            ->with(['client', 'manager'])
            ->withCount('serviceRequests')
            ->when($user->isStaff(), function ($q) use ($user) {
                // Staff only see projects they belong to or manage.
                $q->where(function ($qq) use ($user) {
                    $qq->where('project_manager_id', $user->id)
                        ->orWhereHas('users', fn ($u) => $u->whereKey($user->id));
                });
            })
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->priority($request->query('priority'))
            ->forClient($request->integer('client_id') ?: null)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        return view('projects.create', $this->formOptions(new Project([
            'status' => ProjectStatus::Planning,
            'priority' => Priority::Medium,
            'currency' => 'USD',
        ])));
    }

    public function store(ProjectRequest $request)
    {
        $project = Project::create($request->validated());

        ActivityLog::record('project.created', $project, "Created project {$project->name}");

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['client', 'manager', 'users'])
            ->load(['serviceRequests' => fn ($q) => $q->latest()])
            ->load(['tasks' => fn ($q) => $q->with('assignee')->latest()]);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.edit', $this->formOptions($project));
    }

    public function update(ProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        ActivityLog::record('project.updated', $project, "Updated project {$project->name}");

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        ActivityLog::record('project.archived', $project, "Archived project {$project->name}");

        return redirect()->route('projects.index')->with('success', 'Project archived.');
    }

    public function restore(Project $project)
    {
        $this->authorize('restore', $project);

        $project->restore();

        ActivityLog::record('project.restored', $project, "Restored project {$project->name}");

        return redirect()->route('projects.show', $project)->with('success', 'Project restored.');
    }

    /**
     * Shared option lists for the create/edit form.
     *
     * @return array<string, mixed>
     */
    private function formOptions(Project $project): array
    {
        return [
            'project' => $project,
            'clients' => Client::orderBy('company_name')->get(),
            'managers' => User::whereIn('role', [UserRole::Manager->value, UserRole::Admin->value])->orderBy('name')->get(),
        ];
    }
}
