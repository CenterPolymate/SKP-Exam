<?php

use App\Livewire\Actions\Logout;
use App\Models\Exam;
use App\Models\Position;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $targetType = '';

    public string $status = '';

    public string $positionId = '';

    public int $perPage = 10;

    public ?int $deletingId = null;

    public string $deleteError = '';

    public function search_(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'targetType', 'status', 'positionId');
        $this->resetPage();
    }

    #[Computed]
    public function positions()
    {
        return Position::orderBy('title')->get();
    }

    #[Computed]
    public function totalCount(): int
    {
        return Exam::count();
    }

    #[Computed]
    public function publishedCount(): int
    {
        return Exam::where('status', 'published')->count();
    }

    #[Computed]
    public function draftCount(): int
    {
        return Exam::where('status', 'draft')->count();
    }

    #[Computed]
    public function exams()
    {
        return Exam::query()
            ->withCount('questions')
            ->with('positions')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->targetType !== '', fn ($query) => $query->where('target_type', $this->targetType))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->positionId !== '', fn ($query) => $query->whereHas('positions', fn ($q) => $q->where('positions.id', (int) $this->positionId)))
            ->latest('updated_at')
            ->paginate($this->perPage);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->deleteError = '';
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->deleteError = '';
    }

    public function delete(): void
    {
        $exam = Exam::find($this->deletingId);
        $this->deletingId = null;

        if (! $exam) {
            return;
        }

        if ($exam->attempts()->exists()) {
            $this->deleteError = 'ไม่สามารถลบข้อสอบนี้ได้ เนื่องจากมีผู้สมัครทำข้อสอบชุดนี้ไปแล้ว';

            return;
        }

        $exam->delete();
    }

    public function thaiDate(?\Illuminate\Support\Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        return "{$date->day} {$months[$date->month]} ".($date->year + 543);
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="flex min-h-screen bg-[#FAF8F3]">
    <x-admin.sidebar />

    <main class="min-w-0 flex-1 p-6 lg:p-10">
        <h1 class="text-2xl font-bold text-stone-900">คลังข้อสอบ</h1>
        <p class="mt-1 text-sm text-stone-500">จัดการชุดข้อสอบสำหรับการคัดเลือกผู้สมัคร</p>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-stone-200 bg-white p-4">
                <p class="text-sm text-stone-400">ข้อสอบทั้งหมด</p>
                <p class="mt-1 text-3xl font-bold text-stone-900">{{ $this->totalCount }}</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-4">
                <p class="text-sm text-stone-400">เผยแพร่แล้ว</p>
                <p class="mt-1 text-3xl font-bold text-green-600">{{ $this->publishedCount }}</p>
            </div>
            <div class="rounded-xl border border-stone-200 bg-white p-4">
                <p class="text-sm text-stone-400">ฉบับร่าง</p>
                <p class="mt-1 text-3xl font-bold text-amber-600">{{ $this->draftCount }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-stone-200 bg-white p-4">
            <label class="text-sm text-stone-500">ตัวคัดกรอง</label>
            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-4">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" wire:model="search" placeholder="ค้นหาชื่อข้อสอบ"
                           class="w-full rounded-lg border-stone-300 py-2.5 pl-9 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                </div>

                <select wire:model="targetType" class="rounded-lg border-stone-300 py-2.5 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    <option value="">ประเภท: ทั้งหมด</option>
                    <option value="mandatory">บังคับ</option>
                    <option value="position_specific">ความถนัด</option>
                </select>

                <select wire:model="status" class="rounded-lg border-stone-300 py-2.5 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    <option value="">สถานะ: ทั้งหมด</option>
                    <option value="published">เผยแพร่แล้ว</option>
                    <option value="draft">ฉบับร่าง</option>
                </select>

                <select wire:model="positionId" class="rounded-lg border-stone-300 py-2.5 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    <option value="">ตำแหน่ง: ทั้งหมด</option>
                    @foreach ($this->positions as $position)
                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <a href="{{ route('admin.exams.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-lg bg-[#BA772A] px-5 py-2 text-sm font-semibold text-white hover:bg-[#a6661f]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    สร้างข้อสอบใหม่
                </a>

                <div class="flex items-center gap-4">
                    <button wire:click="clearFilters" type="button" class="text-sm text-stone-500 hover:text-stone-700">
                        ล้างตัวกรอง
                    </button>
                    <button wire:click="search_" type="button" class="rounded-lg bg-[#BA772A] px-5 py-2 text-sm font-semibold text-white hover:bg-[#a6661f]">
                        ค้นหา
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-4 text-sm text-stone-500">
            แสดง {{ $this->exams->firstItem() ?? 0 }}-{{ $this->exams->lastItem() ?? 0 }} จาก {{ $this->exams->total() }} ชุด
        </p>

        @if ($deleteError)
            <div class="mt-3 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ $deleteError }}
            </div>
        @endif

        <div class="mt-3 overflow-x-auto rounded-xl border border-stone-200 bg-white">
            <table class="w-full min-w-[820px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50 text-stone-500">
                        <th class="px-5 py-3 font-medium">ชื่อข้อสอบ</th>
                        <th class="px-5 py-3 font-medium">ประเภท</th>
                        <th class="px-5 py-3 font-medium">ตำแหน่งที่ผูก</th>
                        <th class="px-5 py-3 font-medium">คำถาม/เวลา</th>
                        <th class="px-5 py-3 font-medium">สถานะ</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($this->exams as $exam)
                        <tr class="align-top text-stone-700">
                            <td class="px-5 py-3">
                                <p class="font-medium text-stone-900">{{ $exam->name }}</p>
                                @if ($exam->description)
                                    <p class="mt-0.5 text-xs text-stone-400">{{ \Illuminate\Support\Str::limit($exam->description, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($exam->target_type === 'mandatory')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-600">🔒 บังคับ</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">@ ความถนัด</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                {{ $exam->target_type === 'mandatory' ? 'ทุกตำแหน่ง' : ($exam->positions->pluck('title')->join(', ') ?: '-') }}
                            </td>
                            <td class="px-5 py-3">{{ $exam->questions_count }} ข้อ &middot; {{ $exam->duration_minutes }} นาที</td>
                            <td class="px-5 py-3">
                                @if ($exam->status === 'published')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span> เผยแพร่แล้ว
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span> ฉบับร่าง
                                    </span>
                                @endif
                                <p class="mt-1 text-xs text-stone-400">อัปเดต {{ $this->thaiDate($exam->updated_at) }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.exams.edit', $exam) }}" wire:navigate title="แก้ไข" class="text-stone-400 hover:text-[#BA772A]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>
                                    <button wire:click="confirmDelete({{ $exam->id }})" type="button" title="ลบ" class="text-stone-400 hover:text-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-stone-400">ยังไม่มีข้อสอบในระบบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->exams->lastPage() > 1)
            <div class="mt-4 flex items-center justify-end gap-2">
                <button wire:click="previousPage" @disabled($this->exams->onFirstPage()) type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-stone-500 hover:bg-stone-100 disabled:opacity-40">
                    &larr; ย้อนกลับ
                </button>

                @if ($this->exams->lastPage() <= 7)
                    @for ($p = 1; $p <= $this->exams->lastPage(); $p++)
                        <button wire:click="gotoPage({{ $p }})" type="button"
                                class="h-8 w-8 rounded-lg text-sm font-medium {{ $p === $this->exams->currentPage() ? 'bg-[#BA772A] text-white' : 'text-stone-500 hover:bg-stone-100' }}">
                            {{ $p }}
                        </button>
                    @endfor
                @else
                    <span class="text-sm text-stone-500">หน้า {{ $this->exams->currentPage() }} จาก {{ $this->exams->lastPage() }}</span>
                @endif

                <button wire:click="nextPage" @disabled($this->exams->onLastPage()) type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-stone-500 hover:bg-stone-100 disabled:opacity-40">
                    ถัดไป &rarr;
                </button>
            </div>
        @endif
    </main>

    {{-- Delete confirmation modal --}}
    @if ($deletingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold text-stone-900">ยืนยันการลบข้อสอบ</h3>
                <p class="mt-2 text-sm text-stone-500">ต้องการลบข้อสอบชุดนี้ใช่หรือไม่? การลบไม่สามารถย้อนกลับได้</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="cancelDelete" class="rounded-lg px-4 py-2 text-sm font-medium text-stone-500 hover:bg-stone-100">
                        ยกเลิก
                    </button>
                    <button type="button" wire:click="delete" wire:loading.attr="disabled" wire:target="delete"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">
                        ยืนยันลบ
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
