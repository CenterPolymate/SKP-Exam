<?php

use App\Livewire\Actions\ExamLogout;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.exam-app')] class extends Component
{
    public ExamAttempt $attempt;

    public bool $acknowledged = false;

    public function mount(ExamAttempt $attempt): void
    {
        abort_unless($attempt->exam_assignment_id === Auth::guard('exam_applicant')->id(), 403);

        if (! in_array($attempt->status, ['not_started', 'in_progress'], true)) {
            $this->redirect(route('exam.dashboard'), navigate: true);

            return;
        }

        $this->attempt = $attempt->load('exam.questions');
    }

    public function startExam(): void
    {
        if (! $this->acknowledged) {
            return;
        }

        if ($this->attempt->status === 'not_started') {
            $this->attempt->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        $this->redirect(route('exam.attempts.take', $this->attempt), navigate: true);
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
        <a href="{{ route('exam.dashboard') }}" wire:navigate class="text-sm font-medium text-stone-500 hover:text-stone-700">
            &larr; กลับไปหน้ารวมข้อสอบ
        </a>

        <div class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
            @if ($attempt->exam->target_type === 'mandatory')
                <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-600">🔒 บังคับ</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">@ ความถนัด</span>
            @endif
            <h1 class="mt-2 text-xl font-bold text-stone-900">{{ $attempt->exam->name }}</h1>

            <div class="mt-4 grid grid-cols-3 divide-x divide-stone-100 rounded-xl border border-stone-200 py-4 text-center">
                <div>
                    <p class="text-lg font-bold text-stone-800">{{ $attempt->exam->duration_minutes }} นาที</p>
                    <p class="text-xs text-stone-400">เวลาในการทำ</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-stone-800">{{ $attempt->exam->questions->count() }} ข้อ</p>
                    <p class="text-xs text-stone-400">จำนวนข้อสอบ</p>
                </div>
                <div>
                    <p class="text-lg font-bold text-stone-800">{{ rtrim(rtrim(number_format((float) $attempt->exam->pass_percentage, 2), '0'), '.') }}%</p>
                    <p class="text-xs text-stone-400">เกณฑ์ผ่าน</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-amber-50 p-4">
                <p class="flex items-center gap-2 text-sm font-semibold text-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    สิ่งที่ควรรู้ก่อนเริ่ม
                </p>
                <ul class="mt-2 space-y-1.5 text-sm text-amber-800">
                    <li>• เมื่อเริ่มทำข้อสอบแล้ว ตัวจับเวลาจะเริ่มนับทันทีและไม่สามารถหยุดได้</li>
                    <li>• เมื่อหมดเวลา ระบบจะส่งคำตอบที่ทำไว้ให้อัตโนมัติ</li>
                    <li>• กรุณาอย่าปิดหน้าต่างหรือรีเฟรชขณะทำข้อสอบ ระบบจะบันทึกคำตอบให้อัตโนมัติทุกครั้งที่ตอบ</li>
                    <li>• เมื่อส่งคำตอบแล้วจะไม่สามารถกลับมาทำซ้ำได้</li>
                    <li>• ระบบจะบันทึกไว้หากสลับแท็บ/หน้าต่างระหว่างทำข้อสอบ</li>
                </ul>
            </div>

            <div class="mt-4 rounded-xl border border-stone-200 p-4">
                <p class="flex items-center gap-2 text-sm font-semibold text-stone-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9 12l2 2 4-4"></path></svg>
                    วิธีทำข้อสอบ
                </p>
                <ul class="mt-2 space-y-1.5 text-sm text-stone-600">
                    <li>✓ อ่านคำถามให้ครบและเลือกคำตอบที่ถูกที่สุด</li>
                    <li>✓ สามารถข้ามข้อและกลับมาทำทีหลังได้</li>
                    <li>✓ ใช้ปุ่ม "ทำเครื่องหมาย" เพื่อทำเครื่องหมายข้อที่ยังไม่มั่นใจ</li>
                    <li>✓ ระบบจะบันทึกคำตอบอัตโนมัติ (Auto-save) ทุกครั้ง</li>
                </ul>
            </div>

            <label class="mt-4 flex items-center gap-2 text-sm text-stone-700">
                <input type="checkbox" wire:model.live="acknowledged" class="rounded border-stone-300 text-[#BA772A] focus:ring-[#BA772A]">
                ฉันอ่านและเข้าใจทั้งหมดแล้ว
            </label>

            <div class="mt-4 flex gap-3">
                <a href="{{ route('exam.dashboard') }}" wire:navigate class="flex w-1/3 items-center justify-center rounded-lg border border-stone-200 px-4 py-3 text-sm font-semibold text-stone-600 hover:bg-stone-50">
                    &larr; กลับ
                </a>
                <button type="button" wire:click="startExam"
                        @disabled(! $acknowledged)
                        class="flex-1 rounded-lg bg-[#BA772A] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#a6661f] disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400">
                    เริ่มทำข้อสอบ
                </button>
            </div>
        </div>
    </main>
</div>
