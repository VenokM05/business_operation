<x-app-layout>
    <x-slot name="title">Search</x-slot>
    <form method="GET" action="{{ route('search') }}" class="panel flex flex-wrap items-end gap-3 mb-6">
        <div class="flex-1 min-w-48"><label for="search-term" class="field-label">Search clients, projects, and requests</label><input id="search-term" name="q" type="search" value="{{ $term }}" class="field" required maxlength="255" placeholder="Company, project, request number…"></div><button class="action">Search</button>
    </form>
    @if ($term === '')
        <div class="panel empty-state">Enter a search term. Only records you can access will appear.</div>
    @else
        <p class="text-sm text-muted mb-5">Results for “{{ $term }}” · Limited to your authorized records</p>
        <div class="grid lg:grid-cols-3 gap-5">
            @foreach ($groups as $resource => $group)
                <section class="panel min-w-0">
                    <h2 class="section-title mb-4">{{ ['clients' => 'Clients', 'projects' => 'Projects', 'service-requests' => 'Service Requests'][$resource] }} <span class="badge-neutral">{{ $group['total'] }}</span></h2>
                    <ul class="divide-y divide-line">
                        @forelse ($group['items'] as $item)
                            <li class="py-3 text-sm break-words"><a href="{{ route($resource.'.show', $item) }}" class="text-link">{{ $item->company_name ?? $item->name ?? $item->request_number.' · '.$item->title }}</a></li>
                        @empty
                            <li class="py-3 text-sm text-muted">No matches.</li>
                        @endforelse
                    </ul>
                    @if ($group['total'] > 0)<a href="{{ route($resource.'.index', ['q' => $term]) }}" class="text-link text-sm inline-block mt-5">View all {{ $group['total'] }} results →</a>@endif
                </section>
            @endforeach
        </div>
    @endif
</x-app-layout>
