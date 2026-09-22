<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\ProjectStatus;
use App\Http\Requests\ListingRequest;
use App\Http\Requests\ProjectRequest;
use App\Http\Requests\ProjectTeamRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(ListingRequest $request)
    {
        $this->authorize('viewAny', Project::class);

        $user = $request->user();

        $projects = Project::query()
            ->with(['client', 'manager'])
            ->withCount(['serviceRequests' => fn ($q) => $q->visibleTo($user)])
            ->visibleTo($user)
            ->when($request->query('archive') === 'archived', fn ($q) => $q->onlyTrashed())
            ->when($request->filled('from'), fn ($q) => $q->whereDate('start_date', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('start_date', '<=', $request->query('to')))
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->priority($request->query('priority'))
            ->forClient($request->integer('client_id') ?: null)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $clients = Client::visibleTo($user)->orderBy('company_name')->get();

        return view('projects.index', compact('projects', 'clients'));
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
        $project = $this->service->save(new Project, $request->validated(), $request->user());

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['client', 'manager', 'users'])
            ->load(['serviceRequests' => fn ($q) => $q->visibleTo(request()->user())->latest()])
            ->load(['tasks' => fn ($q) => $q->visibleTo(request()->user())->with('assignee')->latest()]);
        $staff = request()->user()->can('assignStaff', $project) ? User::orderBy('name')->get() : collect();
        $logs = request()->user()->can('viewAny', ActivityLog::class)
            ? ActivityLog::whereMorphedTo('entity', $project)->with('user')->latest()->orderByDesc('id')->paginate(10)
            : collect();

        return view('projects.show', compact('project', 'staff', 'logs'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.edit', $this->formOptions($project));
    }

    public function update(ProjectRequest $request, Project $project)
    {
        $this->service->save($project, $request->validated(), $request->user());

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function assignStaff(ProjectTeamRequest $request, Project $project)
    {
        $this->service->assignTeam($project, $request->validated('staff_ids', []), $request->user());

        return back()->with('success', 'Project team updated.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        DB::transaction(function () use ($project) {
            $project->delete();
            ActivityLog::record('project.archived', $project, "Archived project {$project->name}");
        });

        return redirect()->route('projects.index')->with('success', 'Project archived.');
    }

    public function restore(Project $project)
    {
        $this->authorize('restore', $project);

        DB::transaction(function () use ($project) {
            $project->restore();
            ActivityLog::record('project.restored', $project, "Restored project {$project->name}");
        });

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
            'managers' => User::orderBy('name')->get(),
        ];
    }
}
