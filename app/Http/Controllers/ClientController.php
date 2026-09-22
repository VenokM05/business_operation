<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatus;
use App\Http\Requests\ClientRequest;
use App\Http\Requests\ListingRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(ListingRequest $request)
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::visibleTo($request->user())
            ->when($request->query('archive') === 'archived', fn ($q) => $q->onlyTrashed())
            ->withCount([
                'projects' => fn ($q) => $q->visibleTo($request->user()),
                'serviceRequests' => fn ($q) => $q->visibleTo($request->user()),
            ])
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->industry($request->query('industry'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $industries = Client::visibleTo($request->user())
            ->when($request->query('archive') === 'archived', fn ($q) => $q->onlyTrashed())
            ->whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry');

        return view('clients.index', compact('clients', 'industries'));
    }

    public function create()
    {
        $this->authorize('create', Client::class);

        return view('clients.create', ['client' => new Client(['status' => ClientStatus::Active])]);
    }

    public function store(ClientRequest $request)
    {
        $client = DB::transaction(function () use ($request) {
            $client = Client::create($request->validated());
            ActivityLog::record('client.created', $client, "Created client {$client->company_name}");

            return $client;
        });

        return redirect()->route('clients.show', $client)->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load([
            'projects' => fn ($q) => $q->visibleTo(request()->user())->latest(),
            'serviceRequests' => fn ($q) => $q->visibleTo(request()->user())->latest(),
        ]);
        $logs = request()->user()->can('viewAny', ActivityLog::class)
            ? ActivityLog::whereMorphedTo('entity', $client)->with('user')->latest()->orderByDesc('id')->paginate(10)
            : collect();

        return view('clients.show', compact('client', 'logs'));
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client)
    {
        DB::transaction(function () use ($request, $client) {
            $client->update($request->validated());
            ActivityLog::record('client.updated', $client, "Updated client {$client->company_name}");
        });

        return redirect()->route('clients.show', $client)->with('success', 'Client updated successfully.');
    }

    /** Archive = soft delete (PRD Section 5). */
    public function destroy(Request $request, Client $client)
    {
        $this->authorize('delete', $client);

        DB::transaction(function () use ($client) {
            $client->delete();
            ActivityLog::record('client.archived', $client, "Archived client {$client->company_name}");
        });

        return redirect()->route('clients.index')->with('success', 'Client archived.');
    }

    public function restore(Client $client)
    {
        $this->authorize('restore', $client);

        DB::transaction(function () use ($client) {
            $client->restore();
            ActivityLog::record('client.restored', $client, "Restored client {$client->company_name}");
        });

        return redirect()->route('clients.show', $client)->with('success', 'Client restored.');
    }
}
