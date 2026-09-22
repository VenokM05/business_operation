<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListingRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\ServiceRequest;

class SearchController extends Controller
{
    public function __invoke(ListingRequest $request)
    {
        $term = trim($request->validated('q') ?? '');
        $groups = [];
        if ($term !== '') {
            foreach (['clients' => Client::class, 'projects' => Project::class, 'service-requests' => ServiceRequest::class] as $key => $model) {
                $this->authorize('viewAny', $model);
                $query = $model::visibleTo($request->user())->search($term);
                $groups[$key] = ['total' => (clone $query)->count(), 'items' => $query->latest()->limit(5)->get()];
            }
        }

        return view('search.index', compact('term', 'groups'));
    }
}
