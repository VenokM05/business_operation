<?php

namespace App\Http\Controllers;

use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Http\Requests\ListingRequest;
use App\Http\Requests\TaskRequest;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(ListingRequest $request)
    {
        $this->authorize('viewAny', Task::class);
        $tasks = Task::visibleTo($request->user())->with(['assignee', 'taskable'])
            ->search($request->query('q'))->status($request->query('status'))
            ->assignedTo($request->integer('assigned_to') ?: null)
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->query('priority')))
            ->when($request->query('archive') === 'archived', fn ($q) => $q->onlyTrashed())
            ->when($request->filled('from'), fn ($q) => $q->whereDate('due_date', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('due_date', '<=', $request->query('to')))
            ->latest()->paginate(10)->withQueryString();
        $staff = User::when($request->user()->isStaff(), fn ($q) => $q->whereKey($request->user()->id))->orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'staff'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        return view('tasks.form', $this->formOptions(new Task(['status' => TaskStatus::ToDo, 'priority' => Priority::Medium])));
    }

    public function store(TaskRequest $request)
    {
        $task = DB::transaction(function () use ($request) {
            $type = $request->validated('parent_type') === 'project' ? Project::class : ServiceRequest::class;
            $parent = $type::lockForUpdate()->findOrFail($request->validated('parent_id'));
            $this->authorize('view', $parent);
            $data = $request->safe()->only(['title', 'description', 'priority', 'status', 'due_date', 'assigned_to']);
            if ($request->user()->isStaff()) {
                $data['assigned_to'] = $request->user()->id;
            }
            $task = $parent->tasks()->create([...$data, 'created_by' => $request->user()->id]);
            ActivityLog::record('task.created', $task, "Created task {$task->title}");

            return $task;
        });

        return redirect()->route('tasks.show', $task)->with('success', 'Task created.');
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        $task->load(['assignee', 'creator', 'taskable']);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        return view('tasks.form', $this->formOptions($task));
    }

    public function update(TaskRequest $request, Task $task)
    {
        DB::transaction(function () use ($request, $task) {
            $current = Task::lockForUpdate()->findOrFail($task->id);
            $this->authorize('update', $current);
            $fields = ['title', 'description', 'priority', 'status', 'due_date'];
            if (! $request->user()->isStaff()) {
                $fields[] = 'assigned_to';
            }
            $oldStatus = $current->status;
            $current->update($request->safe()->only($fields));
            ActivityLog::record('task.updated', $current, "Updated task {$current->title} ({$oldStatus->label()} → {$current->status->label()})");
        });

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        DB::transaction(function () use ($task) {
            $current = Task::lockForUpdate()->findOrFail($task->id);
            $this->authorize('delete', $current);
            $current->delete();
            ActivityLog::record('task.archived', $current, "Archived task {$current->title}");
        });

        return redirect()->route('tasks.index')->with('success', 'Task archived.');
    }

    public function restore(Task $task)
    {
        $this->authorize('restore', $task);
        DB::transaction(function () use ($task) {
            $task->restore();
            ActivityLog::record('task.restored', $task, "Restored task {$task->title}");
        });

        return redirect()->route('tasks.show', $task)->with('success', 'Task restored.');
    }

    private function formOptions(Task $task): array
    {
        $user = request()->user();

        return [
            'task' => $task,
            'projects' => $task->exists ? collect() : Project::visibleTo($user)->orderBy('name')->get(),
            'requests' => $task->exists ? collect() : ServiceRequest::visibleTo($user)->latest()->get(),
            'staff' => $user->isStaff() ? collect() : User::orderBy('name')->get(),
        ];
    }
}
