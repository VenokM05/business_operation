<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListingRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\User;

class ActivityLogController extends Controller
{
    public function __invoke(ListingRequest $request)
    {
        $this->authorize('viewAny', ActivityLog::class);
        $types = ['client' => Client::class, 'project' => Project::class, 'request' => ServiceRequest::class, 'task' => Task::class];
        $logs = ActivityLog::with('user')
            ->when($request->filled('q'), fn ($q) => $q->where('description', 'like', '%'.$request->query('q').'%'))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->query('action')))
            ->when($request->filled('entity'), fn ($q) => $q->where('entity_type', $types[$request->query('entity')]))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to')))
            ->latest()->orderByDesc('id')->paginate(20)->withQueryString();
        $actors = User::orderBy('name')->get(['id', 'name']);
        $actions = ActivityLog::distinct()->orderBy('action')->pluck('action');

        return view('activity-logs.index', compact('logs', 'actors', 'actions'));
    }
}
