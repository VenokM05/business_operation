<x-app-layout>
    <x-slot name="title">Edit Project</x-slot>
    <div class="panel max-w-3xl">
        <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('projects._form')
            <div class="flex items-center gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('projects.show', $project) }}" class="text-link text-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
