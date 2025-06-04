<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @if(request()->routeIs('dashboard'))
                Dashboard
            @elseif(request()->routeIs('request.create'))
                Submit Form
            @elseif(request()->routeIs('approvals.index'))
                Approvals
            @elseif(request()->routeIs('approver-assignments.index'))
                Manage Approvers
            @else
                {{ config('app.name', 'A-Sync') }}
            @endif
        </title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/lyceum-logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Add Alpine.js directly (fallback) -->
        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            .dropdown-loading {
                opacity: 0.5;
                pointer-events: none;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <!-- Loading indicator -->
        <div x-data="{ loading: true }" 
             x-init="window.addEventListener('load', () => loading = false)"
             x-cloak>
            <div x-show="loading" 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-90">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
            </div>
        </div>

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Toast Notifications -->
            @if (session('success'))
                <x-toast-notification type="success" :message="session('success')" />
            @endif

            @if (session('error'))
                <x-toast-notification type="error" :message="session('error')" />
            @endif

            @if (session('info'))
                <x-toast-notification type="info" :message="session('info')" />
            @endif

            @if (session('warning'))
                <x-toast-notification type="warning" :message="session('warning')" />
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <script>
            // Fallback for network issues
            window.addEventListener('error', function(e) {
                if (e.target.tagName === 'SCRIPT') {
                    console.warn('Script loading failed:', e.target.src);
                    document.body.classList.remove('dropdown-loading');
                }
            }, true);
        </script>
    </body>
</html>
