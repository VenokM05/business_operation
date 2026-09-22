<x-app-layout>
    <x-slot name="title">Edit Project</x-slot>
    <div class="bg-white rounded-lg shadow-sm p-6 max-w-3xl">
        <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('projects._form')
            <div class="flex items-center gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
