<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Http\Requests\ServiceRequestRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    public function __construct(
        private readonly RequestWorkflowService $workflow,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $user = $request->user();

        $requests = ServiceRequest::query()
            ->with(['client', 'assignee'])
            ->when($user->isStaff(), function ($q) use ($user) {
                $q->where(fn ($qq) => $qq->where('assigned_to', $user->id)->orWhere('created_by', $user->id));
            })
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->priority($request->query('priority'))
            ->assignedTo($request->integer('assigned_to') ?: null)
            ->forClient($request->integer('client_id') ?: null)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('service-requests.index', compact('requests'));
    }

    public function create()
    {
        $this->authorize('create', ServiceRequest::class);

        return view('service-requests.create', $this->formOptions(new ServiceRequest([
            'status' => RequestStatus::New,
            'priority' => \App\Enums\Priority::Medium,
        ])));
    }

    public function store(ServiceRequestRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['status'] = RequestStatus::New;

        $serviceRequest = ServiceRequest::create($data);

        ActivityLog::record('request.created', $serviceRequest, "Created request {$serviceRequest->request_number}");

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Service request created successfully.');
    }

    public function show(ServiceRequest $service_request)
    {
        $this->authorize('view', $service_request);

        $service_request->load(['client', 'project', 'assignee', 'creator'])
            ->load(['updates.user'])
            ->load(['tasks.assignee']);

        return view('service-requests.show', [
            'request' => $service_request,
            'allowedTransitions' => collect($service_request->status->allowedTransitions()),
        ]);
    }

    public function assign(Request $request, ServiceRequest $service_request)
    {
        $this->authorize('assignStaff', $service_request);

        $data = $request->validate([
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $assignee = isset($data['assigned_to']) ? User::find($data['assigned_to']) : null;

        $this->workflow->assign($service_request, $assignee, $request->user());

        return back()->with('success', $assignee ? "Assigned to {$assignee->name}." : 'Assignee removed.');
    }

    public function updateStatus(Request $request, ServiceRequest $service_request)
    {
        $this->authorize('changeStatus', $service_request);

        $data = $request->validate([
            'status' => ['required', Rule::enum(RequestStatus::class)],
        ]);

        $this->workflow->changeStatus($service_request, RequestStatus::from($data['status']), $request->user());

        return back()->with('success', 'Status updated.');
    }

    public function addComment(Request $request, ServiceRequest $service_request)
    {
        $this->authorize('update', $service_request);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->workflow->addComment($service_request, $data['message'], $request->user());

        return back()->with('success', 'Comment added.');
    }

    public function destroy(ServiceRequest $service_request)
    {
        $this->authorize('delete', $service_request);

        $service_request->delete();

        ActivityLog::record('request.archived', $service_request, "Archived request {$service_request->request_number}");

        return redirect()->route('service-requests.index')->with('success', 'Request archived.');
    }

    public function restore(ServiceRequest $service_request)
    {
        $this->authorize('restore', $service_request);

        $service_request->restore();

        return redirect()->route('service-requests.show', $service_request)->with('success', 'Request restored.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(ServiceRequest $serviceRequest): array
    {
        return [
            'request' => $serviceRequest,
            'clients' => Client::orderBy('company_name')->get(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'client_id']),
            'staff' => User::whereIn('role', [UserRole::Staff->value, UserRole::Manager->value])->orderBy('name')->get(),
        ];
    }
}
