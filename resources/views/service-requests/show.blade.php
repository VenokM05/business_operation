<x-app-layout>
    <x-slot name="title">{{ $request->request_number }}</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-500">
            {{ $request->client?->company_name }} @if($request->project) · <a href="{{ route('projects.show', $request->project) }}" class="text-indigo-600 hover:underline">{{ $request->project->name }}</a> @endif
        </div>
        @can('delete', $request)
            <form method="POST" action="{{ route('service-requests.destroy', $request) }}" onsubmit="return confirm('Archive this request?');">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-md hover:bg-red-500">Archive</button>
            </form>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ $request->title }}</h2>
                        <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $request->description ?: 'No description provided.' }}</p>
                    </div>
                    <span class="shrink-0 px-2 py-1 rounded-full text-xs bg-indigo-50 text-indigo-700">{{ $request->status?->label() }}</span>
                </div>
                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 text-sm">
                    <div><dt class="text-gray-400">Category</dt><dd class="text-gray-800">{{ $request->category?->label() }}</dd></div>
                    <div><dt class="text-gray-400">Priority</dt><dd class="text-gray-800">{{ $request->priority?->label() }}</dd></div>
                    <div><dt class="text-gray-400">Assignee</dt><dd class="text-gray-800">{{ $request->assignee?->name ?: 'Unassigned' }}</dd></div>
                    <div><dt class="text-gray-400">Due</dt><dd class="text-gray-800">{{ $request->due_date?->format('M d, Y') ?: '—' }}</dd></div>
                </dl>
            </div>

            {{-- Activity timeline (PRD Section 9) --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Activity</h2>
                <ol class="relative border-l border-gray-200 space-y-5 ml-3">
                    @forelse ($request->updates as $update)
                        <li class="ml-5">
                            <span class="absolute -left-1.5 mt-1.5 w-3 h-3 rounded-full bg-indigo-400"></span>
                            <div class="text-sm text-gray-700">
                                <span class="font-medium">{{ $update->user?->name ?: 'System' }}</span>
                                {{ $update->message }}
                            </div>
                            <div class="text-xs text-gray-400">{{ $update->created_at?->format('M d, Y g:i A') }}</div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400">No activity yet.</li>
                    @endforelse
                </ol>

                @can('update', $request)
                    <form method="POST" action="{{ route('service-requests.comments', $request) }}" class="mt-6 flex gap-2">
                        @csrf
                        <input type="text" name="message" placeholder="Add a progress note…" class="flex-1 rounded-md border-gray-300 text-sm" required maxlength="2000">
                        <x-primary-button>Comment</x-primary-button>
                    </form>
                @endcan
            </div>
        </div>

        {{-- Sidebar actions --}}
        <div class="space-y-6">
            {{-- Status transition --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Change Status</h2>
                @can('changeStatus', $request)
                    @if ($allowedTransitions->isEmpty())
                        <p class="text-sm text-gray-400">No further transitions ({{ $request->status?->label() }} is terminal).</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($allowedTransitions as $to)
                                <form method="POST" action="{{ route('service-requests.status', $request) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $to->value }}">
                                    <button class="px-3 py-1.5 text-sm bg-gray-800 text-white rounded-md hover:bg-gray-700">{{ $to->label() }}</button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-400">You don't have permission to change status.</p>
                @endcan
                @error('status')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Assignment --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-3">Assigned Staff</h2>
                @can('assignStaff', $request)
                    <form method="POST" action="{{ route('service-requests.assign', $request) }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <select name="assigned_to" class="block w-full rounded-md border-gray-300 text-sm">
                            <option value="">— Unassigned —</option>
                            @foreach (App\Models\User::whereIn('role', ['staff','manager'])->orderBy('name')->get() as $member)
                                <option value="{{ $member->id }}" @selected($request->assigned_to == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <x-primary-button>Save</x-primary-button>
                    </form>
                @else
                    <p class="text-sm text-gray-600">{{ $request->assignee?->name ?: 'Unassigned' }}</p>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
