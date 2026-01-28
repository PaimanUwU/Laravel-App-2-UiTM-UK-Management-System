<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar Container -->
        <div class="relative z-auto w-64 flex-shrink-0 bg-white border-r border-zinc-200">
            @include('layouts.sidebar')
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Mobile Header -->
            <header
                class="lg:hidden flex items-center justify-between h-16 px-4 bg-white border-b border-zinc-200 flex-shrink-0">
                <div class="lg:hidden"></div>

                <div class="flex items-center space-x-2">
                    <x-application-logo class="w-6 h-6 fill-current text-zinc-800" />
                </div>

                @auth
                    <flux:dropdown position="top" align="end">
                        <button
                            class="flex items-center justify-center w-8 h-8 rounded-full bg-zinc-100 text-zinc-600 font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </button>

                        <flux:menu>
                            <flux:menu.item icon="user" href="{{ route('profile.edit') }}">{{ __('Profile') }}
                            </flux:menu.item>
                            <flux:menu.separator />
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <flux:menu.item icon="arrow-right-start-on-rectangle" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                @endauth
            </header>

            <main class="flex-1 overflow-y-auto p-4 lg:p-8 ">
                @isset($header)
                    <div class="mb-8">
                        {{ $header }}
                    </div>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </div>

    @fluxScripts
</body>

</html>