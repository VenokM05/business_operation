@php
    $user = auth()->user();
@endphp

<aside class="lg:w-64 lg:shrink-0 bg-gray-800 lg:h-screen lg:sticky lg:top-0 z-50
             fixed inset-y-0 left-0 w-64 transform transition-transform duration-200 ease-in-out
             lg:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       x-cloak>
    <div class="flex items-center gap-2 h-16 px-4 border-b border-gray-700">
        <span class="text-white font-bold text-lg tracking-tight">BOMS</span>
    </div>

    <nav class="p-3 space-y-1">
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
            Dashboard
        </x-sidebar-link>

        <x-sidebar-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4M9 20H4v-2a4 4 0 014-4m5-3a4 4 0 100-8 4 4 0 000 8z"/></svg>
            Clients
        </x-sidebar-link>

        <x-sidebar-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
            Projects
        </x-sidebar-link>

        <x-sidebar-link :href="route('service-requests.index')" :active="request()->routeIs('service-requests.*')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Service Requests
        </x-sidebar-link>
    </nav>

    <div class="absolute bottom-0 inset-x-0 p-4 border-t border-gray-700">
        <div class="text-sm text-gray-300">{{ $user->name }}</div>
        <div class="text-xs text-gray-500 capitalize">{{ $user->role?->label() }}</div>
    </div>
</aside>
