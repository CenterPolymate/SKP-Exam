<?php

use App\Models\Applicant;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin')] class extends Component
{
    public Applicant $applicant;

    public function mount(Applicant $applicant): void
    {
        $this->applicant = $applicant->load([
            'position', 'education', 'languages', 'workHistories', 'references', 'documents',
        ]);
    }

    protected function decide(string $status, string $flashMessage): void
    {
        $this->applicant->update([
            'status' => $status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        session()->flash('status', $flashMessage);

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function approve(): void
    {
        $this->decide('qualified', "อนุมัติใบสมัครของ {$this->applicant->first_name_th} {$this->applicant->last_name_th} แล้ว (ผ่านเกณฑ์)");
    }

    public function reject(): void
    {
        $this->decide('disqualified', "ปฏิเสธใบสมัครของ {$this->applicant->first_name_th} {$this->applicant->last_name_th} แล้ว (ไม่ผ่านเกณฑ์)");
    }

    public function downloadPdf()
    {
        $pdf = app('dompdf.wrapper')->loadView('pdf.application', ['applicant' => $this->applicant]);

        $filename = 'APP-'.str_pad($this->applicant->id, 6, '0', STR_PAD_LEFT).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function logout(\App\Livewire\Actions\Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="flex min-h-screen bg-[#FAF8F3]" x-data="{ confirming: null }">
    <x-admin.sidebar />

    <main class="min-w-0 flex-1 p-6 lg:p-10">
        <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-medium text-stone-500 hover:text-stone-700">
            &larr; กลับไปหน้ารายชื่อ
        </a>

        <div class="mt-3 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-stone-900">
                    {{ $applicant->title_th }}{{ $applicant->first_name_th }} {{ $applicant->last_name_th }}
                </h1>
                <p class="mt-1 text-sm text-stone-500">
                    สมัครตำแหน่ง {{ $applicant->position->title ?? '-' }} &middot; ใบสมัครเลขที่ APP-{{ str_pad($applicant->id, 6, '0', STR_PAD_LEFT) }}
                </p>
            </div>

            <button
                type="button"
                wire:click="downloadPdf"
                wire:loading.attr="disabled"
                wire:target="downloadPdf"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="downloadPdf">ดาวน์โหลด PDF</span>
                <span wire:loading wire:target="downloadPdf">กำลังสร้างไฟล์...</span>
            </button>
        </div>

        <div class="mt-6 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8">
            @include('partials.applicant-review', ['a' => $applicant])

            <div class="mt-10 flex flex-col-reverse items-center justify-end gap-3 border-t border-stone-100 pt-6 sm:flex-row">
                <button
                    type="button"
                    @click="confirming = 'reject'"
                    class="w-full rounded-lg border border-red-300 bg-red-50 px-6 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100 sm:w-auto"
                >
                    ปฏิเสธ
                </button>
                <button
                    type="button"
                    @click="confirming = 'approve'"
                    class="w-full rounded-lg bg-green-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-700 sm:w-auto"
                >
                    อนุมัติ
                </button>
            </div>
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
            <h3 class="text-lg font-semibold text-stone-900" x-text="confirming === 'approve' ? 'ยืนยันการอนุมัติใบสมัคร' : 'ยืนยันการปฏิเสธใบสมัคร'"></h3>
            <p class="mt-2 text-sm text-stone-500" x-text="confirming === 'approve' ? 'ใบสมัครนี้จะถูกตั้งสถานะเป็น \'ผ่านเกณฑ์\' และย้ายออกจากรายการผู้สมัครที่รอตรวจสอบ' : 'ใบสมัครนี้จะถูกตั้งสถานะเป็น \'ไม่ผ่านเกณฑ์\' และย้ายออกจากรายการผู้สมัครที่รอตรวจสอบ'"></p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="confirming = null" class="rounded-lg px-4 py-2 text-sm font-medium text-stone-500 hover:bg-stone-100">
                    ยกเลิก
                </button>
                <button
                    type="button"
                    x-show="confirming === 'reject'"
                    wire:click="reject"
                    wire:loading.attr="disabled"
                    wire:target="reject"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60"
                >
                    ยืนยันปฏิเสธ
                </button>
                <button
                    type="button"
                    x-show="confirming === 'approve'"
                    wire:click="approve"
                    wire:loading.attr="disabled"
                    wire:target="approve"
                    class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 disabled:opacity-60"
                >
                    ยืนยันอนุมัติ
                </button>
            </div>
        </div>
    </div>
</div>
