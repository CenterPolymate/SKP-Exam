<?php

use App\Livewire\Actions\ExamLogout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.exam-app')] class extends Component
{
    public bool $justSubmitted = false;

    #[Computed]
    public function assignment()
    {
        return auth('exam_applicant')->user()->load([
            'applicant.position',
            'attempts' => fn ($query) => $query->with(['exam' => fn ($q) => $q->withCount('questions')])->orderBy('id'),
        ]);
    }

    public function examStatusMeta(string $status): array
    {
        return in_array($status, ['not_started', 'in_progress'], true)
            ? ['done' => false]
            : ['done' => true];
    }

    public function thaiDateTime(?\Illuminate\Support\Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        return "{$date->day} {$months[$date->month]} ".($date->year + 543).' - '.$date->format('H:i');
    }

    public function thaiDate(?\Illuminate\Support\Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        return "{$date->day} {$months[$date->month]} ".($date->year + 543);
    }

    public function confirmSubmit(): void
    {
        $assignment = $this->assignment();

        $allDone = $assignment->attempts->every(fn ($attempt) => ! in_array($attempt->status, ['not_started', 'in_progress'], true));

        if (! $allDone || $assignment->attempts->isEmpty()) {
            return;
        }

        $assignment->applicant->update(['status' => 'awaiting_review']);

        $this->justSubmitted = true;
    }

    public function logout(ExamLogout $logout): void
    {
        $logout();

        $this->redirect(route('exam.login'), navigate: true);
    }
}; ?>

<div class="min-h-screen bg-[#FAF3E7] pb-16">
    <header class="border-b border-stone-200 bg-white px-6 py-4">
        <img src="{{ asset('images/logos/grand-sk.png') }}" alt="Grand SK" class="h-9 w-auto object-contain">
    </header>

    <main class="mx-auto max-w-2xl px-4 py-8">
        @php $assignment = $this->assignment; @endphp
        @if (! $justSubmitted)
            <div class="rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-lg font-bold text-amber-700">
                            {{ mb_substr($assignment->applicant->first_name_th ?? '?', 0, 1) }}
                        </span>
                        <div>
                            <p class="font-semibold text-stone-900">
                                {{ $assignment->applicant->title_th }}{{ $assignment->applicant->first_name_th }} {{ $assignment->applicant->last_name_th }}
                            </p>
                            <p class="text-xs text-stone-400">
                                APP-{{ str_pad($assignment->applicant->id, 6, '0', STR_PAD_LEFT) }} &middot; {{ $assignment->applicant->position->title ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <button wire:click="logout" type="button" class="inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-[#BA772A]">
                            ออกจากระบบ
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </button>
                        <p class="mt-1 text-xs text-stone-400">หมดอายุการเข้าระบบ: {{ $this->thaiDate($assignment->exam_deadline) }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ($assignment->attempts as $attempt)
                        @php $meta = $this->examStatusMeta($attempt->status); @endphp
                        <div class="flex items-center justify-between rounded-xl border border-stone-200 p-4">
                            <div>
                                <p class="font-medium text-stone-800">{{ $attempt->exam->name }}</p>
                                <div class="mt-1 flex items-center gap-2 text-xs text-stone-400">
                                    <span>{{ $attempt->exam->questions_count }} ข้อ</span>
                                    @if ($attempt->exam->target_type === 'mandatory')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5 font-medium text-stone-600">🔒 บังคับ</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 font-medium text-blue-700">@ ความถนัด</span>
                                    @endif
                                </div>
                            </div>

                            @if ($meta['done'])
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    สอบเสร็จสิ้น
                                </span>
                            @else
                                <a href="{{ route($attempt->status === 'in_progress' ? 'exam.attempts.take' : 'exam.attempts.start', $attempt) }}" wire:navigate
                                   class="rounded-full border border-[#BA772A] px-3 py-1.5 text-xs font-semibold text-[#BA772A] hover:bg-amber-50">
                                    {{ $attempt->status === 'in_progress' ? 'ทำต่อ' : 'เริ่มการสอบ' }} &rarr;
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

                @php
                    $allDone = $assignment->attempts->isNotEmpty() && $assignment->attempts->every(fn ($a) => ! in_array($a->status, ['not_started', 'in_progress'], true));
                @endphp

                <button
                    wire:click="confirmSubmit"
                    type="button"
                    @disabled(! $allDone)
                    class="mt-6 w-full rounded-lg bg-[#BA772A] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#a6661f] disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400"
                >
                    ยืนยันการส่ง
                </button>
            </div>
        @else
            <div class="rounded-2xl bg-white p-8 text-center shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l1.8 4.6L18 8l-4.2 1.4L12 14l-1.8-4.6L6 8l4.2-1.4L12 2z"></path><path d="M5 17l.9 2.1L8 20l-2.1.9L5 23l-.9-2.1L2 20l2.1-.9L5 17z"></path></svg>
                </div>
                <h1 class="mt-4 text-lg font-bold text-stone-900">ส่งข้อสอบเรียบร้อยแล้ว!</h1>
                <p class="mt-2 text-sm text-stone-500">
                    ขอบคุณที่สละเวลาทำแบบทดสอบทุกชุด ตอนนี้ทีมงานที่เกี่ยวข้องจะดำเนินการตรวจสอบและติดต่อกลับไปในลำดับถัดไปค่ะ
                </p>

                <div class="mt-6 space-y-2 rounded-xl border border-stone-200 p-4 text-left text-sm">
                    <div class="flex justify-between">
                        <span class="text-stone-400">ผู้สมัคร</span>
                        <span class="font-medium text-stone-800">{{ $assignment->applicant->title_th }}{{ $assignment->applicant->first_name_th }} {{ $assignment->applicant->last_name_th }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-400">ตำแหน่ง</span>
                        <span class="font-medium text-stone-800">{{ $assignment->applicant->position->title ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-400">ข้อสอบทั้งสิ้น</span>
                        <span class="font-medium text-stone-800">{{ $assignment->attempts->count() }} ชุด</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-400">ส่งเมื่อ</span>
                        <span class="font-medium text-stone-800">{{ $this->thaiDateTime(now()) }}</span>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-left text-xs text-green-700">
                    เจ้าหน้าที่ HR จะตรวจข้อสอบและติดต่อกลับเรื่องผลภายใน 3-5 วันทำการ
                </div>

                <button wire:click="logout" type="button" class="mt-6 inline-flex items-center gap-1 text-sm font-medium text-[#BA772A] hover:text-[#a6661f]">
                    ออกจากระบบ &rarr;
                </button>
            </div>
        @endif

        <p class="mt-6 text-center text-xs text-stone-400">&copy; {{ date('Y') + 543 }} SK Polymer Co., Ltd. All rights reserved.</p>
    </main>
</div>
