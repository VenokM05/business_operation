<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\RequestStatus;
use App\Http\Requests\ListingRequest;
use App\Http\Requests\RequestActionRequest;
use App\Http\Requests\ServiceRequestRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    public function __construct(
        private readonly RequestWorkflowService $workflow,
    ) {}

    public function index(ListingRequest $request)
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $user = $request->user();

        $requests = ServiceRequest::query()
            ->with(['client', 'assignee'])
            ->visibleTo($user)
            ->when($request->query('archive') === 'archived', fn ($q) => $q->onlyTrashed())
            ->when($request->filled('from'), fn ($q) => $q->whereDate('due_date', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('due_date', '<=', $request->query('to')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->query('category')))
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->priority($request->query('priority'))
            ->assignedTo($request->integer('assigned_to') ?: null)
            ->forClient($request->integer('client_id') ?: null)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $clients = Client::visibleTo($user)->orderBy('company_name')->get();
        $staff = User::when($user->isStaff(), fn ($q) => $q->whereIn('id',
            ServiceRequest::visibleTo($user)->select('assigned_to')->whereNotNull('assigned_to')
        ))->orderBy('name')->get();

        return view('service-requests.index', compact('requests', 'clients', 'staff'));
    }

    public function create()
    {
        $this->authorize('create', ServiceRequest::class);

        return view('service-requests.create', $this->formOptions(new ServiceRequest([
            'status' => RequestStatus::New,
            'priority' => Priority::Medium,
        ])));
    }

    public function store(ServiceRequestRequest $request)
    {
        $serviceRequest = $this->workflow->create($request->safe()->only([
            'title', 'description', 'client_id', 'project_id', 'category', 'priority', 'due_date', 'assigned_to',
        ]), $request->user());

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Service request created successfully.');
    }

    public function show(ServiceRequest $service_request)
    {
        $this->authorize('view', $service_request);

        $service_request->load(['client', 'project', 'assignee', 'creator'])
            ->load(['tasks' => fn ($q) => $q->visibleTo(request()->user())->with('assignee')->latest()]);

        return view('service-requests.show', [
            'request' => $service_request,
            'updates' => $service_request->updates()->with('user')->paginate(15),
            'staff' => request()->user()->can('assignStaff', $service_request) ? User::orderBy('name')->get() : collect(),
            'allowedTransitions' => collect($service_request->status->allowedTransitions()),
        ]);
    }

    public function edit(ServiceRequest $service_request)
    {
        $this->authorize('update', $service_request);

        return view('service-requests.edit', ['request' => $service_request]);
    }

    public function update(ServiceRequestRequest $request, ServiceRequest $service_request)
    {
        DB::transaction(function () use ($request, $service_request) {
            $current = ServiceRequest::lockForUpdate()->findOrFail($service_request->id);
            $this->authorize('update', $current);
            $current->update($request->safe()->only(['title', 'description', 'category', 'priority', 'due_date']));
            ActivityLog::record('request.updated', $current, "Updated request {$current->request_number}");
        });

        return redirect()->route('service-requests.show', $service_request)->with('success', 'Request details updated.');
    }

    public function assign(RequestActionRequest $request, ServiceRequest $service_request)
    {
        $this->authorize('assignStaff', $service_request);

        $data = $request->validated();

        $assignee = isset($data['assigned_to']) ? User::find($data['assigned_to']) : null;

        $this->workflow->assign($service_request, $assignee, $request->user());

        return back()->with('success', $assignee ? "Assigned to {$assignee->name}." : 'Assignee removed.');
    }

    public function updateStatus(RequestActionRequest $request, ServiceRequest $service_request)
    {
        $this->authorize('changeStatus', $service_request);

        $data = $request->validated();

        $this->workflow->changeStatus($service_request, RequestStatus::from($data['status']), $request->user());

        return back()->with('success', 'Status updated.');
    }

    public function addComment(RequestActionRequest $request, ServiceRequest $service_request)
    {
        $this->authorize('update', $service_request);

        $data = $request->validated();

        $this->workflow->addComment($service_request, $data['message'], $request->user());

        return back()->with('success', 'Comment added.');
    }

    public function destroy(ServiceRequest $service_request)
    {
        $this->authorize('delete', $service_request);

        DB::transaction(function () use ($service_request) {
            $service_request->delete();
            ActivityLog::record('request.archived', $service_request, "Archived request {$service_request->request_number}");
        });

        return redirect()->route('service-requests.index')->with('success', 'Request archived.');
    }

    public function restore(ServiceRequest $service_request)
    {
        $this->authorize('restore', $service_request);

        DB::transaction(function () use ($service_request) {
            $service_request->restore();
            ActivityLog::record('request.restored', $service_request, "Restored request {$service_request->request_number}");
        });

        return redirect()->route('service-requests.show', $service_request)->with('success', 'Request restored.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(ServiceRequest $serviceRequest): array
    {
        return [
            'request' => $serviceRequest,
            'clients' => Client::visibleTo(request()->user())->orderBy('company_name')->get(),
            'projects' => Project::visibleTo(request()->user())->orderBy('name')->get(['id', 'name', 'client_id']),
            'staff' => request()->user()->isStaff() ? collect() : User::orderBy('name')->get(),
        ];
    }
}
