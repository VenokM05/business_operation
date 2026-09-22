<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BOMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-black/50 lg:hidden"
             x-cloak @click="sidebarOpen = false"></div>

        <div class="min-h-screen lg:flex">
            {{-- Sidebar --}}
            @include('layouts.sidebar')

            {{-- Main column --}}
            <div class="flex-1 flex flex-col min-w-0">
                {{-- Top bar --}}
                <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3">
                            <button class="lg:hidden text-gray-500 hover:text-gray-700" @click="sidebarOpen = true" aria-label="Open menu">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? config('app.name') }}</h1>
                        </div>

                        {{-- User menu --}}
                        <div class="flex items-center">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
                                        <span>{{ auth()->user()->name }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
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
                </header>

                {{-- Flash messages --}}
                <div class="px-4 sm:px-6 lg:px-8 pt-4">
                    @if (session('success'))
                        <div class="mb-3 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-3 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any() && ! $errors->has('status'))
                        {{-- Form errors are shown inline; nothing global here. --}}
                    @endif
                </div>

                {{-- Page content --}}
                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
