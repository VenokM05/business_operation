<x-app-layout>
    <x-slot name="title">New Service Request</x-slot>

    <div class="bg-white rounded-lg shadow-sm p-6 max-w-3xl">
        <form method="POST" action="{{ route('service-requests.store') }}" class="space-y-6" x-data="{ clientId: @js((string) old('client_id', '')), projectId: @js((string) old('project_id', '')) }">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="title" value="Title *" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="client_id" value="Client *" />
                    <select id="client_id" name="client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" x-model="clientId" @change="projectId = ''" required>
                        <option value="">— Select client —</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->company_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('client_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="project_id" value="Project (optional)" />
                    <select id="project_id" name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" x-model="projectId">
                        <option value="">— None —</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" x-show="clientId === '{{ $project->client_id }}'" :disabled="clientId !== '{{ $project->client_id }}'" @selected(old('project_id') == $project->id)>{{ $project->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('project_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="category" value="Category *" />
                    <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        @foreach (App\Enums\RequestCategory::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="priority" value="Priority *" />
                    <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        @foreach (App\Enums\Priority::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('priority')" class="mt-1" />
                </div>

                @can('assignStaff', $request)
                <div>
                    <x-input-label for="assigned_to" value="Assign To" />
                    <select id="assigned_to" name="assigned_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">— Unassigned —</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}" @selected(old('assigned_to') == $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('assigned_to')" class="mt-1" />
                </div>
                @endcan

                <div>
                    <x-input-label for="due_date" value="Due Date" />
                    <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date')" />
                    <x-input-error :messages="$errors->get('due_date')" class="mt-1" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Create Request</x-primary-button>
                <a href="{{ route('service-requests.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
