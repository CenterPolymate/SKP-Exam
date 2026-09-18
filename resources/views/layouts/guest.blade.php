<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Grand SK') }} - เข้าสู่ระบบเจ้าหน้าที่</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|prompt:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased" style="font-family: 'Prompt', 'Figtree', ui-sans-serif, system-ui, sans-serif;">
        <div class="min-h-screen flex flex-col bg-[#FDFBF3]">

            {{-- Header --}}
            <header class="flex items-center justify-center border-b border-stone-100 bg-white/60 py-4">
                <a href="{{ url('/') }}" wire:navigate>
                    <img src="{{ asset('images/logos/grand-sk.png') }}" alt="Grand SK" class="h-10 w-auto object-contain">
                </a>
            </header>

            <div class="flex flex-1 flex-col items-center justify-center px-4 py-10">
                <div class="w-full sm:max-w-md rounded-2xl border border-stone-200 bg-white px-6 py-8 shadow-sm sm:px-8">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs text-stone-400">
                    ระบบสำหรับเจ้าหน้าที่ Grand SK เท่านั้น &middot; หากพบปัญหาการเข้าสู่ระบบ กรุณาติดต่อฝ่ายทรัพยากรบุคคล
                </p>
            </div>
        </div>
    </body>
</html>
