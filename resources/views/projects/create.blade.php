<x-app-layout>
    <x-slot name="title">New Project</x-slot>
    <div class="panel max-w-3xl">
        <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
            @csrf
            @include('projects._form')
            <div class="flex items-center gap-3">
                <x-primary-button>Create Project</x-primary-button>
                <a href="{{ route('projects.index') }}" class="text-link text-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
