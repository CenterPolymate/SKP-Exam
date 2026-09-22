@php
    $navItems = [
        ['label' => 'หน้าหลัก', 'route' => 'dashboard', 'available' => true],
        ['label' => 'ตำแหน่งที่เปิดรับสมัคร', 'route' => 'admin.positions.index', 'available' => true],
        ['label' => 'คลังข้อสอบ', 'route' => 'admin.exams.index', 'match' => 'admin.exams.*', 'available' => true],
        ['label' => 'จัดการบัญชีผู้สมัคร', 'route' => 'admin.applicant-accounts.index', 'available' => true],
        ['label' => 'ตรวจข้อสอบ', 'route' => 'admin.exam-review.index', 'match' => 'admin.exam-review.*', 'available' => true],
        ['label' => 'สรุปผล', 'route' => 'admin.summary.index', 'available' => true],
    ];
@endphp

<aside class="hidden w-64 shrink-0 flex-col justify-between border-r border-stone-200 bg-white md:flex">
    <div>
        <div class="flex items-center justify-center px-6 py-6">
            <img src="{{ asset('images/logos/grand-sk.png') }}" alt="Grand SK" class="h-16 w-auto object-contain">
        </div>

        <nav class="mt-2 space-y-1 px-4">
            @foreach ($navItems as $item)
                @if ($item['available'])
                    <a href="{{ route($item['route']) }}" wire:navigate
                       class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs($item['match'] ?? $item['route']) ? 'bg-amber-50 text-[#BA772A]' : 'text-stone-600 hover:bg-stone-50' }}">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="flex items-center rounded-lg px-3 py-2.5 text-sm font-medium text-stone-300">
                        {{ $item['label'] }}
                    </span>
                @endif
            @endforeach
        </nav>
    </div>

    <div class="border-t border-stone-200 p-4">
        <div class="flex items-center justify-between rounded-lg px-2 py-2">
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-stone-800">{{ auth()->user()->name }}</p>
                <p class="text-xs text-stone-400">Admin Access</p>
            </div>
            <button wire:click="logout" title="ออกจากระบบ" class="rounded-md p-2 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </button>
        </div>
    </div>
</aside>
