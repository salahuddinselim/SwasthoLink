<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <script>
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-brand-50">

            <!-- Desktop sidebar -->
            <aside class="hidden lg:flex lg:fixed lg:inset-y-0 lg:left-0 lg:w-64 lg:flex-col bg-brand-900 z-30">
                @include('layouts.sidebar')
            </aside>

            <!-- Mobile sidebar (slide-over) -->
            <div x-show="sidebarOpen" x-cloak class="lg:hidden fixed inset-0 z-40" role="dialog" aria-modal="true">
                <div x-show="sidebarOpen"
                     x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition-opacity ease-linear duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     @click="sidebarOpen = false" class="fixed inset-0 bg-gray-900/60"></div>

                <div x-show="sidebarOpen"
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                     class="fixed inset-y-0 left-0 w-64 bg-brand-900 flex flex-col">
                    <button @click="sidebarOpen = false" class="absolute -right-11 top-3 p-2 text-white" aria-label="{{ __('Close menu') }}">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @include('layouts.sidebar')
                </div>
            </div>

            <!-- Main column -->
            <div class="lg:pl-64 flex flex-col min-h-screen">

                <!-- Top bar: hamburger + logo on mobile, just the bell on desktop -->
                <div class="sticky top-0 z-20 flex items-center justify-between bg-white border-b border-gray-200 px-4 h-14">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-gray-600" aria-label="{{ __('Open menu') }}">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                    <span class="font-bold text-brand-700 lg:hidden">SwasthoLink</span>
                    <span class="hidden lg:block"></span>
                    @include('layouts.notifications-bell')
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
