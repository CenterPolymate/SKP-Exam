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
        <div class="flex min-h-screen">

            {{-- Left brand panel --}}
            <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-[#BA772A] px-14 py-12 text-white md:flex">
                <div class="pointer-events-none absolute -left-24 -top-24 h-96 w-96 rounded-full bg-[#BD7D33]"></div>
                <div class="pointer-events-none absolute -bottom-32 -right-16 h-[28rem] w-[28rem] rounded-full bg-[#B17128]"></div>

                <div class="relative text-2xl font-bold tracking-wide">GRAND SK</div>

                <div class="relative">
                    <h1 class="text-4xl font-bold leading-tight lg:text-5xl">
                        ระบบจัดการ<br>การสรรหาบุคลากร
                    </h1>
                    <p class="mt-4 max-w-sm text-white/85">
                        จัดการใบสมัคร ตรวจข้อสอบ และติดตามผลผู้สมัครได้ในที่เดียว
                    </p>
                </div>

                <div class="relative text-sm text-white/70">
                    สำหรับเจ้าหน้าที่ฝ่ายบุคคลเท่านั้น
                </div>
            </div>

            {{-- Right form panel --}}
            <div class="flex w-full flex-col justify-between bg-white px-6 py-10 md:w-1/2 md:px-16 lg:px-24">
                <div class="flex justify-end">
                    <img src="{{ asset('images/logos/grand-sk.png') }}" alt="Grand SK" class="h-10 w-auto object-contain">
                </div>

                <div class="mx-auto w-full max-w-sm">
                    {{ $slot }}
                </div>

                <p class="text-center text-xs text-stone-400">
                    &copy; {{ date('Y') + 543 }} SK Polymer &middot; ระบบภายในองค์กร
                </p>
            </div>
        </div>
    </body>
</html>
