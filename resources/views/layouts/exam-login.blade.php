<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Grand SK') }} - เข้าสู่ระบบสอบ</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|prompt:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased" style="font-family: 'Prompt', 'Figtree', ui-sans-serif, system-ui, sans-serif;">
        <div class="flex min-h-screen items-center justify-center bg-[#FAF3E7] px-4 py-10" x-data="{ contactOpen: false }">
            <div class="w-full max-w-md rounded-2xl bg-white px-6 py-8 shadow-[0px_20px_50px_0px_rgba(0,0,0,0.08)] sm:px-8">
                <div class="flex justify-center">
                    <img src="{{ asset('images/logos/grand-sk.png') }}" alt="Grand SK" class="h-14 w-auto object-contain">
                </div>

                <div class="mt-5 flex items-center justify-center gap-5">
                    <img src="{{ asset('images/logos/sk-polymer.png') }}" alt="SK Polymer" class="h-10 w-auto object-contain">
                    <img src="{{ asset('images/logos/trt.png') }}" alt="TRT" class="h-10 w-auto object-contain">
                    <img src="{{ asset('images/logos/polymate.png') }}" alt="Polymate IT Solutions" class="h-10 w-auto object-contain">
                </div>

                <hr class="mt-6 border-stone-100">

                {{ $slot }}
            </div>

            {{-- Contact us modal --}}
            <div
                x-show="contactOpen"
                x-cloak
                @keydown.escape.window="contactOpen = false"
                class="fixed inset-0 z-50 flex items-center justify-center px-4"
            >
                <div
                    x-show="contactOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="contactOpen = false"
                    class="absolute inset-0 bg-stone-900/50"
                ></div>

                <div
                    x-show="contactOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-sm rounded-2xl bg-white p-8 text-left shadow-xl"
                >
                    <div class="flex items-start justify-between">
                        <h3 class="text-lg font-semibold text-amber-700">ติดต่อเรา</h3>
                        <button
                            type="button"
                            @click="contactOpen = false"
                            class="rounded-full p-1 text-stone-400 transition hover:bg-stone-100 hover:text-stone-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                            aria-label="ปิด"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                        </button>
                    </div>

                    <div class="mt-6 flex flex-col gap-4 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 5.94 2 10.8c0 3.06 1.86 5.76 4.66 7.34-.2.76-.74 2.72-.85 3.14-.13.52.19.51.4.37.16-.11 2.58-1.76 3.63-2.48.68.1 1.38.15 2.16.15 5.52 0 10-3.94 10-8.8S17.52 2 12 2Z"/></svg>
                            </span>
                            <div>
                                <p class="text-stone-400">LINE ID</p>
                                <p class="font-medium text-stone-700">hrm2003</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-.826 1.678l-1.145.573a11.03 11.03 0 0 0 5.516 5.516l.573-1.145a1.5 1.5 0 0 1 1.678-.826l3.223.716A1.5 1.5 0 0 1 18 14.352V15.5a1.5 1.5 0 0 1-1.5 1.5H15c-7.18 0-13-5.82-13-13V3.5Z"/></svg>
                            </span>
                            <div>
                                <p class="text-stone-400">โทร (เจ้าหน้าที่ HR)</p>
                                <a href="tel:0863252003" class="font-medium text-stone-700 hover:text-amber-700">086-325-2003</a>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v.01L10 12l9-5.99V6a2 2 0 0 0-2-2H3Z"/><path d="M18 8.24 10.53 13.1a1 1 0 0 1-1.06 0L2 8.24V14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8.24Z"/></svg>
                            </span>
                            <div>
                                <p class="text-stone-400">Email</p>
                                <a href="mailto:Jobs@skthai.com" class="font-medium text-stone-700 hover:text-amber-700">Jobs@skthai.com</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
