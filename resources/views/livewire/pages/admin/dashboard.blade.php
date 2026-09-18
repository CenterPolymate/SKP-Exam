<?php

use App\Livewire\Actions\Logout;
use App\Models\Applicant;
use App\Models\Position;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $positionId = '';

    public int $perPage = 14;

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function search_(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'positionId');
        $this->resetPage();
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'), navigate: true);
    }

    #[Computed]
    public function totalApplicants(): int
    {
        return Applicant::where('status', 'submitted')->count();
    }

    #[Computed]
    public function positions()
    {
        return Position::orderBy('title')->get();
    }

    #[Computed]
    public function applicants()
    {
        return Applicant::query()
            ->with('position')
            ->where('status', 'submitted')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name_th', 'like', "%{$this->search}%")
                        ->orWhere('last_name_th', 'like', "%{$this->search}%")
                        ->orWhere('first_name_en', 'like', "%{$this->search}%")
                        ->orWhere('last_name_en', 'like', "%{$this->search}%");
                });
            })
            ->when($this->positionId !== '', fn ($query) => $query->where('position_id', (int) $this->positionId))
            ->latest('created_at')
            ->paginate($this->perPage);
    }

    public function statusMeta(string $status): array
    {
        return match ($status) {
            'submitted' => ['label' => 'ส่งใบสมัคร', 'class' => 'bg-stone-100 text-stone-600'],
            'qualified' => ['label' => 'ผ่านเกณฑ์', 'class' => 'bg-green-50 text-green-700'],
            'disqualified' => ['label' => 'ไม่ผ่านเกณฑ์', 'class' => 'bg-red-50 text-red-700'],
            'code_generated' => ['label' => 'สร้างรหัสสอบแล้ว', 'class' => 'bg-amber-50 text-amber-700'],
            'awaiting_review' => ['label' => 'รอตรวจข้อสอบ', 'class' => 'bg-amber-50 text-amber-700'],
            'passed_test' => ['label' => 'ผ่านข้อสอบ', 'class' => 'bg-green-50 text-green-700'],
            'failed_test' => ['label' => 'ไม่ผ่านข้อสอบ', 'class' => 'bg-red-50 text-red-700'],
            default => ['label' => $status, 'class' => 'bg-stone-100 text-stone-600'],
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
}; ?>

<div class="flex min-h-screen bg-[#FAF8F3]">
    <x-admin.sidebar />

    <main class="min-w-0 flex-1 p-6 lg:p-10">
        <h1 class="text-2xl font-bold text-stone-900">รายชื่อผู้สมัคร</h1>
        <p class="mt-1 text-sm text-stone-500">จัดการและติดตามสถานะผู้สมัครทั้งหมด</p>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-[220px_1fr_260px]">
            <div class="rounded-xl border border-stone-200 bg-white p-4">
                <p class="text-sm text-stone-400">จำนวนใบสมัคร</p>
                <p class="mt-1 text-3xl font-bold text-stone-900">{{ $this->totalApplicants }}</p>
            </div>

            <div>
                <label class="text-sm text-stone-500">ตัวคัดกรอง</label>
                <div class="relative mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" wire:model="search" placeholder="ค้นหาชื่อ-นามสกุล"
                           class="w-full rounded-lg border-stone-300 py-2.5 pl-9 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                </div>
            </div>

            <div>
                <label class="text-sm text-stone-500">ตำแหน่งที่ต้องการ</label>
                <select wire:model="positionId" class="mt-1 w-full rounded-lg border-stone-300 py-2.5 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    <option value="">ทั้งหมด</option>
                    @foreach ($this->positions as $position)
                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 flex items-center justify-end gap-4">
            <button wire:click="clearFilters" type="button" class="text-sm text-stone-500 hover:text-stone-700">
                ล้างตัวกรอง
            </button>
            <button wire:click="search_" type="button" class="rounded-lg bg-[#BA772A] px-5 py-2 text-sm font-semibold text-white hover:bg-[#a6661f]">
                ค้นหา
            </button>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-stone-500">
                แสดง {{ $this->applicants->firstItem() ?? 0 }}-{{ $this->applicants->lastItem() ?? 0 }} จาก {{ $this->applicants->total() }} รายการ
            </p>
            <select wire:model.live="perPage" class="rounded-lg border-stone-300 py-1.5 text-sm text-stone-600 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                <option value="10">แสดง 10 แถว</option>
                <option value="14">แสดง 14 แถว</option>
                <option value="25">แสดง 25 แถว</option>
                <option value="50">แสดง 50 แถว</option>
            </select>
        </div>

        <div class="mt-3 overflow-x-auto rounded-xl border border-stone-200 bg-white">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50 text-stone-500">
                        <th class="px-5 py-3 font-medium">ชื่อ-นามสกุล</th>
                        <th class="px-5 py-3 font-medium">ตำแหน่งที่ต้องการ</th>
                        <th class="px-5 py-3 font-medium">เงินเดือน</th>
                        <th class="px-5 py-3 font-medium">วันที่สมัคร</th>
                        <th class="px-5 py-3 font-medium">สถานะ</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($this->applicants as $applicant)
                        @php $status = $this->statusMeta($applicant->status); @endphp
                        <tr class="text-stone-700">
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $applicant->title_th }}{{ $applicant->first_name_th }} {{ $applicant->last_name_th }}</td>
                            <td class="px-5 py-3">{{ $applicant->position->title ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $applicant->expected_salary ? number_format($applicant->expected_salary, 0) : '-' }}</td>
                            <td class="px-5 py-3">{{ $this->thaiDate($applicant->created_at) }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $status['class'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.applicants.show', $applicant) }}" wire:navigate
                                   class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 hover:border-[#BA772A] hover:text-[#BA772A]">
                                    รายละเอียด
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-stone-400">ยังไม่มีผู้สมัครที่ตรงกับเงื่อนไข</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->applicants->lastPage() > 1)
            <div class="mt-4 flex items-center justify-end gap-2">
                <button wire:click="previousPage" @disabled($this->applicants->onFirstPage()) type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-stone-500 hover:bg-stone-100 disabled:opacity-40">
                    &larr; ย้อนกลับ
                </button>

                @if ($this->applicants->lastPage() <= 7)
                    @for ($p = 1; $p <= $this->applicants->lastPage(); $p++)
                        <button wire:click="gotoPage({{ $p }})" type="button"
                                class="h-8 w-8 rounded-lg text-sm font-medium {{ $p === $this->applicants->currentPage() ? 'bg-[#BA772A] text-white' : 'text-stone-500 hover:bg-stone-100' }}">
                            {{ $p }}
                        </button>
                    @endfor
                @else
                    <span class="text-sm text-stone-500">หน้า {{ $this->applicants->currentPage() }} จาก {{ $this->applicants->lastPage() }}</span>
                @endif

                <button wire:click="nextPage" @disabled($this->applicants->onLastPage()) type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-stone-500 hover:bg-stone-100 disabled:opacity-40">
                    ถัดไป &rarr;
                </button>
            </div>
        @endif
    </main>
</div>
