<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientApiController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->visibleTo($request->user())
            ->search($request->query('q'))
            ->status($request->query('status'))
            ->industry($request->query('industry'))
            ->when($request->query('archive') === 'archived', fn ($q) => $q->onlyTrashed())
            ->latest()
            ->paginate($this->perPage());

        $clients->through(fn (Client $c) => (new ClientResource($c))->resolve());

        return $this->paginated($clients, 'Clients retrieved successfully.');
    }

    public function store(ClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return $this->ok((new ClientResource($client))->resolve(), 'Client created successfully.', 201);
    }

    public function show(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return $this->ok((new ClientResource($client))->resolve(), 'Client retrieved successfully.');
    }

    public function update(ClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());

        return $this->ok((new ClientResource($client))->resolve(), 'Client updated successfully.');
    }

    /** Archive (soft delete). Only Admin may force-delete, via the web UI. */
    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);
        $client->delete();

        return $this->ok(null, 'Client archived successfully.');
    }
}
