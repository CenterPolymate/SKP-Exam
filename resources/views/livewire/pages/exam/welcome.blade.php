<?php

use App\Livewire\Actions\ExamLogout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.exam-app')] class extends Component
{
    #[Computed]
    public function assignment()
    {
        return auth('exam_applicant')->user()->load([
            'applicant.position',
            'attempts',
        ]);
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

        <div class="rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8">
            <div class="flex justify-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                </span>
            </div>

            <h1 class="mt-4 text-center text-xl font-bold text-stone-900">ก่อนเริ่มทำแบบทดสอบ</h1>
            <p class="mt-2 text-center text-sm text-stone-500">
                สวัสดีคุณ{{ $assignment->applicant->first_name_th ?? '' }} ยินดีต้อนรับเข้าสู่ระบบทดสอบของ Grand SK<br>
                ขอให้ทำแบบทดสอบด้วยความสบายใจ ไม่ต้องรีบเร่ง เราแค่อยากทำความรู้จักความสามารถของท่านตามจริง
            </p>

            <div class="mt-6 rounded-xl bg-amber-50/60 p-5">
                <h2 class="text-sm font-semibold text-amber-800">ขั้นตอนถัดจากนี้</h2>
                <ol class="mt-3 space-y-3 text-sm text-stone-700">
                    <li class="flex gap-3">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-xs font-bold text-amber-800">1</span>
                        <span>ท่านจะเห็น<strong>รายการชุดข้อสอบ</strong>ที่ได้รับมอบหมายทั้งหมด พร้อมจำนวนข้อและประเภทของแต่ละชุด</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-xs font-bold text-amber-800">2</span>
                        <span>เมื่อกด "เริ่มการสอบ" ในชุดใดก็ตาม ระบบจะแสดง<strong>รายละเอียดของชุดข้อสอบนั้นอีกครั้ง</strong> (จำนวนข้อ เวลาที่ใช้ได้ เกณฑ์ผ่าน) ให้อ่านและกดยืนยันก่อนเริ่มจับเวลาจริง</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-xs font-bold text-amber-800">3</span>
                        <span>เมื่อกดเริ่มแล้ว ระบบจะ<strong>เริ่มจับเวลาทันที</strong> คำตอบของท่านจะถูกบันทึกอัตโนมัติทุกครั้งที่ตอบ ไม่ต้องกังวลเรื่องข้อมูลหาย</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-xs font-bold text-amber-800">4</span>
                        <span>ทำให้ครบทุกชุดที่ได้รับมอบหมาย แล้วกด "ยืนยันการส่ง" ในหน้ารายการข้อสอบเพื่อส่งผลให้ทีมงาน HR ตรวจสอบ</span>
                    </li>
                </ol>
            </div>

            <div class="mt-5 rounded-xl border border-stone-200 p-5">
                <h2 class="text-sm font-semibold text-stone-700">สิ่งที่ควรเตรียมตัวก่อนเริ่ม</h2>
                <ul class="mt-3 space-y-2 text-sm text-stone-600">
                    <li class="flex gap-2">
                        <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-[#BA772A]"></span>
                        <span>อยู่ในที่เงียบสงบ สัญญาณอินเทอร์เน็ตเสถียร ไม่ถูกรบกวนระหว่างทำแบบทดสอบ</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-[#BA772A]"></span>
                        <span>เผื่อเวลาให้เพียงพอสำหรับแบบทดสอบแต่ละชุด เมื่อเริ่มแล้วจะหยุดเวลาไม่ได้</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-[#BA772A]"></span>
                        <span>ทำด้วยตนเองตามความรู้ความสามารถจริง เพื่อประโยชน์ในการพิจารณาของท่านเอง</span>
                    </li>
                </ul>
            </div>

            <a
                href="{{ route('exam.dashboard') }}"
                wire:navigate
                class="mt-8 flex w-full items-center justify-center gap-2 rounded-lg bg-[#BA772A] px-4 py-3 text-center font-semibold text-white shadow-sm transition hover:bg-[#a6661f] focus:outline-none focus:ring-2 focus:ring-[#BA772A] focus:ring-offset-2"
            >
                เข้าใจแล้ว ไปที่รายการข้อสอบ
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>

            <div class="mt-4 text-center">
                <button wire:click="logout" type="button" class="text-sm font-medium text-stone-400 hover:text-stone-600">
                    ออกจากระบบ
                </button>
            </div>
        </div>
    </main>
</div>
