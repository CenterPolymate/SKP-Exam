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
        <div class="min-h-screen bg-[#FDFBF3] flex flex-col items-center px-6 py-16 sm:py-20">

            <div
                class="w-full max-w-3xl flex flex-col items-center text-center"
                x-data="{
                    panel: 'menu',
                    contactOpen: false,
                    lang: (localStorage.getItem('landingLang') === 'en') ? 'en' : 'th',
                    strings: {
                        th: {
                            docTitle: '{{ config('app.name', 'Grand SK') }} - ระบบรับสมัครงาน',
                            welcomePrefix: 'ยินดีต้อนรับสู่',
                            applicantsHeading: 'ผู้สมัครงาน',
                            applicantsSub: 'มาร่วมเป็นส่วนหนึ่งของครอบครัวเรากันเถอะ!',
                            applyBtn: 'สมัครงาน',
                            viewCompanyBtn: 'ดูข้อมูลบริษัท',
                            back: 'กลับ',
                            companyHeading: 'ข้อมูลบริษัท',
                            companySub: 'เลือกบริษัทในเครือที่ต้องการดูข้อมูล',
                            needHelp: 'ต้องการความช่วยเหลือ?',
                            contactUs: 'ติดต่อเรา',
                            close: 'ปิด',
                            phoneLabel: 'โทร (เจ้าหน้าที่ HR)',
                        },
                        en: {
                            docTitle: '{{ config('app.name', 'Grand SK') }} - Job Application System',
                            welcomePrefix: 'Welcome to',
                            applicantsHeading: 'Job Applicants',
                            applicantsSub: 'Come join our family!',
                            applyBtn: 'Apply for a Job',
                            viewCompanyBtn: 'View Company Info',
                            back: 'Back',
                            companyHeading: 'Company Information',
                            companySub: 'Select a subsidiary to view its information',
                            needHelp: 'Need help?',
                            contactUs: 'Contact Us',
                            close: 'Close',
                            phoneLabel: 'Phone (HR Staff)',
                        },
                    },
                    t(key) { return this.strings[this.lang][key] ?? key; },
                    setLang(l) { this.lang = l; localStorage.setItem('landingLang', l); document.cookie = 'landingLang=' + l + '; path=/; max-age=31536000; samesite=lax'; document.title = this.t('docTitle'); },
                }"
                x-init="document.title = t('docTitle')"
            >

                {{-- Language switcher --}}
                <div class="fixed right-4 top-4 z-40 flex items-center gap-1 rounded-full bg-white/90 p-1 shadow-md ring-1 ring-black/5 backdrop-blur">
                    <button type="button" @click="setLang('th')"
                            :class="lang === 'th' ? 'bg-amber-700 text-white' : 'text-stone-500 hover:text-stone-700'"
                            class="rounded-full px-3 py-1 text-xs font-semibold transition">
                        ไทย
                    </button>
                    <button type="button" @click="setLang('en')"
                            :class="lang === 'en' ? 'bg-amber-700 text-white' : 'text-stone-500 hover:text-stone-700'"
                            class="rounded-full px-3 py-1 text-xs font-semibold transition">
                        EN
                    </button>
                </div>

                {{-- Grand SK group logo --}}
                <img
                    src="{{ asset('images/logos/grand-sk.png') }}"
                    alt="Grand SK"
                    class="h-24 sm:h-28 w-auto object-contain"
                >

                {{-- Subsidiary company logos --}}
                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-10 gap-y-4">
                    <img src="{{ asset('images/logos/sk-polymer.png') }}" alt="SK Polymer" class="h-16 sm:h-20 w-auto object-contain">
                    <img src="{{ asset('images/logos/trt.png') }}" alt="TRT" class="h-16 sm:h-20 w-auto object-contain">
                    <img src="{{ asset('images/logos/polymate.png') }}" alt="Polymate IT Solutions" class="h-16 sm:h-20 w-auto object-contain">
                </div>

                {{-- Welcome heading --}}
                <h1 class="mt-10 text-2xl sm:text-3xl font-semibold text-stone-800">
                    <span x-text="t('welcomePrefix')"></span> <span class="text-amber-700">GRAND SK</span>
                </h1>

                {{-- Tagline --}}
                <p class="mt-4 max-w-xl text-sm sm:text-base italic text-stone-500 leading-relaxed">
                    &ldquo;Invest in businesses that enhance the quality of human life through the
                    principles of ethical and righteous business conduct, encompassing morality,
                    ethics, humanity, and justice.&rdquo;
                </p>
                <p class="mt-2 text-xs sm:text-sm font-medium tracking-wide text-amber-700">
                    The Leader of Rubber Products.
                </p>

                {{-- Applicant card --}}
                <div class="mt-12 w-full max-w-sm rounded-2xl bg-white p-8 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-black/5">

                    {{-- Main menu panel --}}
                    <div x-show="panel === 'menu'" x-cloak>
                        <h2 class="text-lg font-semibold text-amber-700" x-text="t('applicantsHeading')"></h2>
                        <p class="mt-1 text-sm text-stone-500" x-text="t('applicantsSub')"></p>

                        <div class="mt-6 flex flex-col gap-3">
                            <a
                                href="{{ route('application.create') }}"
                                wire:navigate
                                class="group flex items-center gap-3 rounded-lg border border-stone-200 px-4 py-3 text-left transition hover:border-amber-500 hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                            >
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
                                <span class="text-sm font-medium text-stone-700 group-hover:text-amber-700" x-text="t('applyBtn')"></span>
                            </a>

                            <button
                                type="button"
                                @click="panel = 'companies'"
                                class="group flex items-center gap-3 rounded-lg border border-stone-200 px-4 py-3 text-left transition hover:border-amber-500 hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                            >
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
                                <span class="text-sm font-medium text-stone-700 group-hover:text-amber-700" x-text="t('viewCompanyBtn')"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Companies panel --}}
                    <div x-show="panel === 'companies'" x-cloak>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="panel = 'menu'"
                                class="shrink-0 rounded-full p-1 text-stone-400 transition hover:bg-stone-100 hover:text-stone-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                :aria-label="t('back')"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" /></svg>
                            </button>
                            <h2 class="text-lg font-semibold text-amber-700" x-text="t('companyHeading')"></h2>
                        </div>
                        <p class="mt-1 text-sm text-stone-500" x-text="t('companySub')"></p>

                        <div class="mt-6 flex flex-col gap-3">
                            <a href="https://grandsk.co.th/" target="_blank" rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border border-stone-200 px-4 py-3 text-left transition hover:border-amber-500 hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
                                <span class="text-sm font-medium text-stone-700 group-hover:text-amber-700">Grand SK</span>
                            </a>
                            <a href="https://skpolymer.com/" target="_blank" rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border border-stone-200 px-4 py-3 text-left transition hover:border-amber-500 hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
                                <span class="text-sm font-medium text-stone-700 group-hover:text-amber-700">SK Polymer</span>
                            </a>
                            <a href="https://thairubbtech.com/" target="_blank" rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border border-stone-200 px-4 py-3 text-left transition hover:border-amber-500 hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
                                <span class="text-sm font-medium text-stone-700 group-hover:text-amber-700">THAI RUBB TECH</span>
                            </a>
                            <a href="https://polymate.co.th/" target="_blank" rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border border-stone-200 px-4 py-3 text-left transition hover:border-amber-500 hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
                                <span class="text-sm font-medium text-stone-700 group-hover:text-amber-700">Polymate</span>
                            </a>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-stone-100 pt-4 text-xs text-stone-400">
                        <span x-text="t('needHelp')"></span>
                        <button type="button" @click="contactOpen = true" class="font-semibold text-stone-600 underline decoration-stone-300 underline-offset-2 hover:text-amber-700" x-text="t('contactUs')"></button>
                    </div>
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
                            <h3 class="text-lg font-semibold text-amber-700" x-text="t('contactUs')"></h3>
                            <button
                                type="button"
                                @click="contactOpen = false"
                                class="rounded-full p-1 text-stone-400 transition hover:bg-stone-100 hover:text-stone-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                :aria-label="t('close')"
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
                                    <p class="text-stone-400" x-text="t('phoneLabel')"></p>
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
        </div>

        @livewireScripts
    </body>
</html>
