<?php

use App\Livewire\Actions\Logout;
use App\Models\Applicant;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public Applicant $applicant;

    public function mount(Applicant $applicant): void
    {
        $this->applicant = $applicant->load([
            'position',
            'examAssignment.attempts.exam.questions',
        ]);
    }

    public function examStatusMeta(string $status): array
    {
        return match ($status) {
            'passed' => ['label' => 'ผ่านการทดสอบ', 'class' => 'bg-green-50 text-green-700', 'graded' => true],
            'failed' => ['label' => 'ไม่ผ่านการทดสอบ', 'class' => 'bg-red-50 text-red-700', 'graded' => true],
            default => ['label' => 'รอตรวจ', 'class' => 'bg-amber-50 text-amber-700', 'graded' => false],
        };
    }

    public function thaiDate(?\Illuminate\Support\Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        return "{$date->day} {$months[$date->month]} ".($date->year + 543);
    }

    public function decidePassed(): void
    {
        $this->applicant->update(['status' => 'passed_test']);

        $this->redirect(route('admin.exam-review.index'), navigate: true);
    }

    public function decideFailed(): void
    {
        $this->applicant->update(['status' => 'failed_test']);

        $this->redirect(route('admin.exam-review.index'), navigate: true);
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="flex min-h-screen bg-[#FAF8F3]" x-data="{ confirming: null }">
    <x-admin.sidebar />

    <main class="min-w-0 flex-1 p-6 lg:p-10">
        <a href="{{ route('admin.exam-review.index') }}" wire:navigate class="text-sm font-medium text-stone-500 hover:text-stone-700">
            &larr; กลับไปรายชื่อการส่ง
        </a>

        <div class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-lg font-bold text-amber-700">
                        {{ mb_substr($applicant->first_name_th, 0, 1) }}
                    </span>
                    <div>
                        <p class="font-semibold text-stone-900">{{ $applicant->title_th }}{{ $applicant->first_name_th }} {{ $applicant->last_name_th }}</p>
                        <p class="text-xs text-stone-400">
                            APP-{{ str_pad($applicant->id, 6, '0', STR_PAD_LEFT) }} &middot; {{ $applicant->position->title ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    @php
                        $overallMeta = match ($applicant->status) {
                            'passed_test' => ['label' => 'ผ่านการทดสอบ', 'class' => 'bg-green-50 text-green-700'],
                            'failed_test' => ['label' => 'ไม่ผ่านการทดสอบ', 'class' => 'bg-red-50 text-red-700'],
                            default => ['label' => 'รอตรวจข้อสอบ', 'class' => 'bg-amber-50 text-amber-700'],
                        };
                        $submittedAt = $applicant->examAssignment?->attempts->max('submitted_at');
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $overallMeta['class'] }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $overallMeta['label'] }}
                    </span>
                    <p class="mt-1 text-xs text-stone-400">ส่งให้เมื่อวันที่: {{ $this->thaiDate($submittedAt) }}</p>
                </div>
            </div>

            <h2 class="mt-6 text-sm font-semibold text-stone-700">เลือกข้อสอบที่จะตรวจ</h2>

            <div class="mt-2 space-y-3">
                @forelse ($applicant->examAssignment?->attempts ?? [] as $attempt)
                    @php $meta = $this->examStatusMeta($attempt->status); @endphp
                    <div class="flex items-center justify-between rounded-xl border border-stone-200 p-4">
                        <div>
                            <p class="font-medium text-stone-800">{{ $attempt->exam->name }}</p>
                            <div class="mt-1 flex items-center gap-2 text-xs text-stone-400">
                                @if ($attempt->score !== null)
                                    <span>{{ rtrim(rtrim(number_format((float) $attempt->score, 2), '0'), '.') }}%</span>
                                @endif
                                <span>{{ $attempt->exam->questions->count() }} ข้อ</span>
                                @if ($attempt->exam->target_type === 'mandatory')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2 py-0.5 font-medium text-stone-600">🔒 บังคับ</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 font-medium text-blue-700">@ ความถนัด</span>
                                @endif
                            </div>
                        </div>

                        @if ($meta['graded'])
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold {{ $meta['class'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                {{ $meta['label'] }}
                            </span>
                        @else
                            <div class="text-right">
                                <span class="block text-xs text-stone-400">ตรวจโดย HR</span>
                                <a href="{{ route('admin.exam-review.grade', $attempt) }}" wire:navigate class="text-sm font-semibold text-[#BA772A] hover:text-[#a6661f]">
                                    ให้คะแนน/แก้ไข &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-stone-400">ไม่พบข้อสอบที่มอบหมายให้ผู้สมัครคนนี้</p>
                @endforelse
            </div>

            @php
                $allGraded = ($applicant->examAssignment?->attempts ?? collect())->isNotEmpty()
                    && ($applicant->examAssignment?->attempts ?? collect())->every(fn ($a) => in_array($a->status, ['passed', 'failed']));
            @endphp

            @if ($applicant->status === 'awaiting_review' && $allGraded)
                <div class="mt-6 flex flex-col-reverse items-center justify-end gap-3 border-t border-stone-100 pt-6 sm:flex-row">
                    <button type="button" @click="confirming = 'failed'"
                            class="w-full rounded-lg border border-red-300 bg-red-50 px-6 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100 sm:w-auto">
                        ไม่ผ่านการทดสอบ
                    </button>
                    <button type="button" @click="confirming = 'passed'"
                            class="w-full rounded-lg bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700 sm:w-auto">
                        ผ่านการทดสอบ
                    </button>
                </div>
            @elseif ($applicant->status === 'awaiting_review')
                <p class="mt-6 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    กรุณาตรวจให้คะแนนข้อสอบทุกชุดให้ครบก่อนจึงจะสรุปผลได้
                </p>
            @endif
        </div>
    </main>

    {{-- Confirmation modal --}}
    <div
        x-show="confirming"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
    >
        <div
            @click.outside="confirming = null"
            x-show="confirming"
            x-transition
            class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl"
        >
            <h3 class="text-lg font-semibold text-stone-900" x-text="confirming === 'passed' ? 'ยืนยันผลผ่านการทดสอบ' : 'ยืนยันผลไม่ผ่านการทดสอบ'"></h3>
            <p class="mt-2 text-sm text-stone-500" x-text="confirming === 'passed' ? 'ใบสมัครนี้จะถูกตั้งสถานะเป็น \'ผ่านการทดสอบ\'' : 'ใบสมัครนี้จะถูกตั้งสถานะเป็น \'ไม่ผ่านการทดสอบ\''"></p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="confirming = null" class="rounded-lg px-4 py-2 text-sm font-medium text-stone-500 hover:bg-stone-100">
                    ยกเลิก
                </button>
                <button
                    type="button"
                    x-show="confirming === 'failed'"
                    wire:click="decideFailed"
                    wire:loading.attr="disabled"
                    wire:target="decideFailed"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60"
                >
                    ยืนยันไม่ผ่าน
                </button>
                <button
                    type="button"
                    x-show="confirming === 'passed'"
                    wire:click="decidePassed"
                    wire:loading.attr="disabled"
                    wire:target="decidePassed"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 disabled:opacity-60"
                >
                    ยืนยันผ่าน
                </button>
            </div>
        </div>
    </div>
</div>
