@props(['resource', 'statuses', 'priorities' => false, 'clients' => null, 'staff' => null, 'industries' => null, 'categories' => false, 'dateLabel' => null])
<form method="GET" action="{{ route($resource.'.index') }}" class="panel mb-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
    <div>
        <label for="filter-q" class="field-label">Search</label>
        <input id="filter-q" type="search" name="q" value="{{ request('q') }}" placeholder="Search records…" maxlength="255" class="field">
    </div>
    <div>
        <label for="filter-status" class="field-label">Status</label>
        <select id="filter-status" name="status" class="field">
            <option value="">All statuses</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    @if ($priorities)
        <div>
            <label for="filter-priority" class="field-label">Priority</label>
            <select id="filter-priority" name="priority" class="field">
                <option value="">All priorities</option>
                @foreach (App\Enums\Priority::options() as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if ($clients !== null)
        <div>
            <label for="filter-client" class="field-label">Client</label>
            <select id="filter-client" name="client_id" class="field">
                <option value="">All clients</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>{{ $client->company_name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if ($staff !== null)
        <div>
            <label for="filter-staff" class="field-label">Assigned staff</label>
            <select id="filter-staff" name="assigned_to" class="field">
                <option value="">All assignees</option>
                @foreach ($staff as $member)
                    <option value="{{ $member->id }}" @selected(request('assigned_to') == $member->id)>{{ $member->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if ($industries !== null)
        <div>
            <label for="filter-industry" class="field-label">Industry</label>
            <select id="filter-industry" name="industry" class="field">
                <option value="">All industries</option>
                @foreach ($industries as $industry)
                    <option value="{{ $industry }}" @selected(request('industry') === $industry)>{{ $industry }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if ($categories)
        <div>
            <label for="filter-category" class="field-label">Category</label>
            <select id="filter-category" name="category" class="field">
                <option value="">All categories</option>
                @foreach (App\Enums\RequestCategory::options() as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if ($dateLabel)
        <div><label for="filter-from" class="field-label">{{ $dateLabel }} from</label><input id="filter-from" type="date" name="from" value="{{ request('from') }}" class="field"></div>
        <div><label for="filter-to" class="field-label">{{ $dateLabel }} to</label><input id="filter-to" type="date" name="to" value="{{ request('to') }}" class="field"></div>
    @endif
    @unless (auth()->user()->isStaff())
        <div>
            <label for="filter-archive" class="field-label">Records</label>
            <select id="filter-archive" name="archive" class="field">
                <option value="active">Not archived</option>
                <option value="archived" @selected(request('archive') === 'archived')>Archived</option>
            </select>
        </div>
    @endunless
    <div class="flex items-center gap-3"><button class="action">Filter</button><a href="{{ route($resource.'.index') }}" class="text-link text-sm">Reset</a></div>
</form>
