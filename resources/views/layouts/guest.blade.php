<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BOMS') }} · Sign in</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased bg-canvas">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            {{-- Showcase panel --}}
            <aside class="brand-gradient relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between lg:p-12">
                <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-accent/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-white/5 blur-3xl"></div>

                <div class="relative">
                    <span class="text-2xl font-extrabold tracking-tight text-white">BOMS</span>
                </div>

                <div class="relative max-w-md">
                    <h2 class="text-3xl font-bold leading-tight tracking-tight text-white">
                        Business Operations,<br>running smoothly.
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed text-white/70">
                        Track clients, projects, service requests and tasks in one place — with role-based access,
                        a full activity trail, and reporting built for operations teams.
                    </p>
                    <ul class="mt-8 space-y-3 text-sm text-white/80">
                        <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10">✓</span> Clients, projects &amp; service requests</li>
                        <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10">✓</span> Task workflows &amp; staff assignment</li>
                        <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10">✓</span> Activity logs, search &amp; reports</li>
                    </ul>
                </div>

                <p class="relative text-xs text-white/50">&copy; {{ date('Y') }} {{ config('app.name', 'BOMS') }}. Internal use only.</p>
            </aside>

            {{-- Form panel --}}
            <main class="flex min-h-screen flex-col justify-center px-6 py-12 sm:px-10 lg:px-16">
                <div class="mx-auto w-full max-w-md">
                    <div class="mb-8 flex justify-center lg:hidden">
                        <img src="{{ asset('assets/logo/boms-logo.png') }}" alt="BOMS — Business Operation Management System" class="h-10 w-auto">
                    </div>

                    <div class="panel shadow-card-hover">
                        <div class="mb-6">
                            <img src="{{ asset('assets/logo/boms-logo.png') }}" alt="BOMS — Business Operation Management System" class="hidden h-9 w-auto lg:block">
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
