<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatus;
use App\Http\Requests\ClientRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->withCount(['projects', 'serviceRequests'])
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->industry($request->query('industry'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $industries = Client::query()->whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry');

        return view('clients.index', compact('clients', 'industries'));
    }

    public function create()
    {
        $this->authorize('create', Client::class);

        return view('clients.create', ['client' => new Client(['status' => ClientStatus::Active])]);
    }

    public function store(ClientRequest $request)
    {
        $client = Client::create($request->validated());

        ActivityLog::record('client.created', $client, "Created client {$client->company_name}");

        return redirect()->route('clients.show', $client)->with('success', 'Client created successfully.');
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load(['projects' => fn ($q) => $q->latest(), 'serviceRequests' => fn ($q) => $q->latest()]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        ActivityLog::record('client.updated', $client, "Updated client {$client->company_name}");

        return redirect()->route('clients.show', $client)->with('success', 'Client updated successfully.');
    }

    /** Archive = soft delete (PRD Section 5). */
    public function destroy(Request $request, Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        ActivityLog::record('client.archived', $client, "Archived client {$client->company_name}");

        return redirect()->route('clients.index')->with('success', 'Client archived.');
    }

    public function restore(Client $client)
    {
        $this->authorize('restore', $client);

        $client->restore();

        ActivityLog::record('client.restored', $client, "Restored client {$client->company_name}");

        return redirect()->route('clients.show', $client)->with('success', 'Client restored.');
    }
}
