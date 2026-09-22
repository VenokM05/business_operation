<x-app-layout>
    <x-slot name="title">New Client</x-slot>

    <div class="bg-white rounded-lg shadow-sm p-6 max-w-3xl">
        <form method="POST" action="{{ route('clients.store') }}" class="space-y-6">
            @csrf
            @include('clients._form')
            <div class="flex items-center gap-3">
                <x-primary-button>Create Client</x-primary-button>
                <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
