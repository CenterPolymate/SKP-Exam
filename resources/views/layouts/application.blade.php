<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Grand SK') }} - ระบบรับสมัครงาน</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600|prompt:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="antialiased" style="font-family: 'Prompt', 'Figtree', ui-sans-serif, system-ui, sans-serif;">
        <div class="min-h-screen bg-[#FDFBF3]">

            {{-- Header --}}
            <header class="flex items-center justify-center border-b border-stone-100 bg-white/60 py-4">
                <a href="{{ url('/') }}" wire:navigate>
                    <img src="{{ asset('images/logos/grand-sk.png') }}" alt="Grand SK" class="h-10 w-auto object-contain">
                </a>
            </header>

            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
