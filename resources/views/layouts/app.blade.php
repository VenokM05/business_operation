<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Dashboard' }} · {{ config('app.name', 'BOMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-canvas text-ink" x-data="{ sidebarOpen: false }">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-brand-dark/40 backdrop-blur-sm lg:hidden"
             @click="sidebarOpen = false"></div>

        <div class="min-h-screen lg:flex">
            {{-- Sidebar --}}
            @include('layouts.sidebar')

            {{-- Main column --}}
            <div class="flex min-w-0 flex-1 flex-col">
                {{-- Top bar --}}
                <header class="sticky top-0 z-30 border-b border-line bg-surface/85 backdrop-blur">
                    <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button class="rounded-lg p-2 text-muted transition hover:bg-canvas hover:text-ink lg:hidden" @click="sidebarOpen = true" aria-label="Open menu">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <h1 class="truncate text-lg font-semibold tracking-tight text-ink">{{ $title ?? config('app.name') }}</h1>
                        </div>

                        {{-- User menu --}}
                        <div class="flex items-center">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-muted transition hover:bg-canvas hover:text-ink">
                                        <span class="avatar h-8 w-8 text-xs">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="border-b border-line px-4 py-3">
                                        <div class="text-sm font-semibold text-ink">{{ auth()->user()->name }}</div>
                                        <div class="truncate text-xs text-muted">{{ auth()->user()->email }}</div>
                                    </div>
                                    <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            Log Out
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('search') }}" role="search" class="flex gap-2 px-4 pb-3 sm:px-6 lg:px-8">
                        <div class="relative w-full max-w-xl">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                            </span>
                            <label for="global-search" class="sr-only">Global search</label>
                            <input id="global-search" type="search" name="q" class="field pl-9" placeholder="Search clients, projects, and requests…" required maxlength="255">
                        </div>
                        <button class="action-secondary shrink-0">Search</button>
                    </form>
                </header>

                {{-- Flash messages --}}
                <div class="space-y-3 px-4 pt-4 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="flex items-start gap-3 rounded-lg border border-emerald-600/20 bg-emerald-50 px-4 py-3 text-sm text-success">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="flex items-start gap-3 rounded-lg border border-red-600/20 bg-red-50 px-4 py-3 text-sm text-danger">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-8.02 13.9A1 1 0 003.13 20h17.74a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div role="alert" class="rounded-lg border border-danger/25 bg-red-50 p-4 text-sm text-danger">
                            <p class="font-semibold">Please correct the following:</p>
                            <ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                </div>

                {{-- Page content --}}
                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
