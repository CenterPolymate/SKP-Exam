<?php

use App\Livewire\Actions\Logout;
use App\Models\Position;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 14;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $title = '';

    public ?int $deletingId = null;

    public string $deleteError = '';

    public function search_(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    #[Computed]
    public function positions()
    {
        return Position::query()
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->orderBy('title')
            ->paginate($this->perPage);
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'title');
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $position = Position::findOrFail($id);

        $this->editingId = $position->id;
        $this->title = $position->title;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        if ($this->editingId) {
            Position::whereKey($this->editingId)->update($validated);
        } else {
            Position::create($validated + ['is_active' => true]);
        }

        $this->showModal = false;
        $this->reset('editingId', 'title');
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
        $position = Position::find($this->deletingId);
        $this->deletingId = null;

        if (! $position) {
            return;
        }

        if ($position->applicants()->exists()) {
            $this->deleteError = 'ไม่สามารถลบตำแหน่งนี้ได้ เนื่องจากมีผู้สมัครงานในตำแหน่งนี้อยู่แล้ว';

            return;
        }

        $position->delete();
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
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-stone-900">ตำแหน่งที่เปิดรับสมัคร</h1>
                <p class="mt-1 text-sm text-stone-500">จัดการตำแหน่งงานสำหรับผูกกับข้อสอบความถนัด</p>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-stone-200 bg-white p-4">
            <label class="text-sm text-stone-500">ตัวคัดกรอง</label>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" wire:model="search" placeholder="ค้นหาตำแหน่ง"
                           class="w-full rounded-lg border-stone-300 py-2.5 pl-9 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                </div>

                <button wire:click="clearFilters" type="button" class="whitespace-nowrap text-sm text-stone-500 hover:text-stone-700">
                    ล้างตัวกรอง
                </button>
                <button wire:click="search_" type="button" class="whitespace-nowrap rounded-lg bg-[#BA772A] px-5 py-2 text-sm font-semibold text-white hover:bg-[#a6661f]">
                    ค้นหา
                </button>
                <button wire:click="openCreate" type="button" class="ml-auto inline-flex items-center gap-2 whitespace-nowrap rounded-lg bg-[#BA772A] px-5 py-2 text-sm font-semibold text-white hover:bg-[#a6661f]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    เพิ่มตำแหน่งใหม่
                </button>
            </div>
        </div>

        <p class="mt-4 text-sm text-stone-500">
            แสดง {{ $this->positions->firstItem() ?? 0 }}-{{ $this->positions->lastItem() ?? 0 }} จาก {{ $this->positions->total() }} ชุด
        </p>

        @if ($deleteError)
            <div class="mt-3 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ $deleteError }}
            </div>
        @endif

        <div class="mt-3 overflow-x-auto rounded-xl border border-stone-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50 text-stone-500">
                        <th class="px-5 py-3 font-medium">ตำแหน่ง</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($this->positions as $position)
                        <tr class="text-stone-700">
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $position->title }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="openEdit({{ $position->id }})" type="button" title="แก้ไข" class="text-stone-400 hover:text-[#BA772A]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $position->id }})" type="button" title="ลบ" class="text-stone-400 hover:text-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-5 py-10 text-center text-stone-400">ยังไม่มีตำแหน่งงาน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->positions->lastPage() > 1)
            <div class="mt-4 flex items-center justify-end gap-2">
                <button wire:click="previousPage" @disabled($this->positions->onFirstPage()) type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-stone-500 hover:bg-stone-100 disabled:opacity-40">
                    &larr; ย้อนกลับ
                </button>

                @if ($this->positions->lastPage() <= 7)
                    @for ($p = 1; $p <= $this->positions->lastPage(); $p++)
                        <button wire:click="gotoPage({{ $p }})" type="button"
                                class="h-8 w-8 rounded-lg text-sm font-medium {{ $p === $this->positions->currentPage() ? 'bg-[#BA772A] text-white' : 'text-stone-500 hover:bg-stone-100' }}">
                            {{ $p }}
                        </button>
                    @endfor
                @else
                    <span class="text-sm text-stone-500">หน้า {{ $this->positions->currentPage() }} จาก {{ $this->positions->lastPage() }}</span>
                @endif

                <button wire:click="nextPage" @disabled($this->positions->onLastPage()) type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-stone-500 hover:bg-stone-100 disabled:opacity-40">
                    ถัดไป &rarr;
                </button>
            </div>
        @endif
    </main>

    {{-- Create / Edit modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold text-stone-900">{{ $editingId ? 'แก้ไขตำแหน่ง' : 'เพิ่มตำแหน่ง' }}</h3>

                <div class="mt-4">
                    <label class="text-sm text-stone-500">ชื่อตำแหน่ง <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="title" placeholder="เช่น Programmer, QA"
                           class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeModal" class="rounded-lg px-4 py-2 text-sm font-medium text-stone-500 hover:bg-stone-100">
                        ยกเลิก
                    </button>
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="rounded-lg bg-[#BA772A] px-5 py-2 text-sm font-semibold text-white hover:bg-[#a6661f] disabled:opacity-60">
                        บันทึก
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($deletingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold text-stone-900">ยืนยันการลบตำแหน่ง</h3>
                <p class="mt-2 text-sm text-stone-500">ต้องการลบตำแหน่งนี้ใช่หรือไม่? การลบไม่สามารถย้อนกลับได้</p>

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
