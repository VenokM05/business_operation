<x-app-layout>
    <x-slot name="title">Edit Client</x-slot>

    <div class="panel max-w-3xl">
        <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('clients._form')
            <div class="flex items-center gap-3">
                <x-primary-button>Save Changes</x-primary-button>
                <a href="{{ route('clients.show', $client) }}" class="text-link text-sm">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
