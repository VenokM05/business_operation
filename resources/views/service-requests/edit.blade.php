<x-app-layout>
    <x-slot name="title">Edit {{ $request->request_number }}</x-slot>
    <div class="panel max-w-3xl">
        <p class="text-sm text-muted mb-6">Edit request details here. Assignment and status changes use the workflow controls on the request page.</p>
        <form method="POST" action="{{ route('service-requests.update', $request) }}" class="space-y-5">
            @csrf @method('PUT')
            <div><label for="title" class="field-label">Title *</label><input id="title" name="title" value="{{ old('title', $request->title) }}" required maxlength="255" class="field"></div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="category" class="field-label">Category *</label>
                    <select id="category" name="category" class="field" required>
                        @foreach (App\Enums\RequestCategory::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $request->category->value) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="priority" class="field-label">Priority *</label>
                    <select id="priority" name="priority" class="field" required>
                        @foreach (App\Enums\Priority::options() as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', $request->priority->value) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label for="due_date" class="field-label">Due date</label><input id="due_date" type="date" name="due_date" class="field" value="{{ old('due_date', $request->due_date?->format('Y-m-d')) }}"></div>
            </div>
            <div><label for="description" class="field-label">Description</label><textarea id="description" name="description" rows="5" class="field" maxlength="5000">{{ old('description', $request->description) }}</textarea></div>
            <div class="flex gap-3"><button class="action">Save Details</button><a href="{{ route('service-requests.show', $request) }}" class="action-secondary">Cancel</a></div>
        </form>
    </div>
</x-app-layout>
