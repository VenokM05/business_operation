<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Project Name *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $project?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="client_id" value="Client *" />
        <select id="client_id" name="client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            <option value="">— Select client —</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" @selected(old('client_id', $project?->client_id) == $client->id)>{{ $client->company_name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('client_id')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="project_manager_id" value="Project Manager" />
        <select id="project_manager_id" name="project_manager_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            <option value="">— Unassigned —</option>
            @foreach ($managers as $manager)
                <option value="{{ $manager->id }}" @selected(old('project_manager_id', $project?->project_manager_id) == $manager->id)>{{ $manager->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('project_manager_id')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="priority" value="Priority *" />
        <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @foreach (App\Enums\Priority::options() as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', optional($project?->priority)->value) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('priority')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @foreach (App\Enums\ProjectStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(old('status', optional($project?->status)->value) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="start_date" value="Start Date" />
        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', $project?->start_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="end_date" value="End Date" />
        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', $project?->end_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="budget" value="Budget" />
        <x-text-input id="budget" name="budget" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('budget', $project?->budget)" />
        <x-input-error :messages="$errors->get('budget')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="currency" value="Currency" />
        <x-text-input id="currency" name="currency" type="text" maxlength="3" class="mt-1 block w-full uppercase" :value="old('currency', $project?->currency ?? 'USD')" required />
        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $project?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>
</div>
