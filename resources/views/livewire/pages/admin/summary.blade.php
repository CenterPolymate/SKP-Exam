<?php

use App\Livewire\Actions\Logout;
use App\Models\Applicant;
use App\Models\ApplicantEducation;
use App\Models\Position;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $tab = 'all';

    // "ดูทุกคนทั้งหมด" filters
    public string $search = '';

    public string $positionId = '';

    public string $gender = '';

    public string $degreeLevel = '';

    public string $minGpa = '';

    public string $yearApplied = '';

    public int $perPage = 14;

    // "ดูจากตำแหน่งงาน" filters
    public string $posSearch = '';

    public string $month = '';

    public string $year = '';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function search_(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'positionId', 'gender', 'degreeLevel', 'minGpa', 'yearApplied');
        $this->resetPage();
    }

    public function clearPositionFilters(): void
    {
        $this->reset('posSearch', 'month', 'year');
    }

    #[Computed]
    public function positions()
    {
        return Position::orderBy('title')->get();
    }

    #[Computed]
    public function appliedYears()
    {
        return Applicant::selectRaw('DISTINCT EXTRACT(YEAR FROM created_at)::int as yr')
            ->orderByDesc('yr')
            ->pluck('yr');
    }

    #[Computed]
    public function applicants()
    {
        $digits = preg_replace('/\D/', '', $this->search);

        return Applicant::query()
            ->with(['position', 'education'])
            ->where('status', '!=', 'draft')
            ->when($this->search !== '', function ($query) use ($digits) {
                $query->where(function ($query) use ($digits) {
                    $query->where('first_name_th', 'like', "%{$this->search}%")
                        ->orWhere('last_name_th', 'like', "%{$this->search}%")
                        ->orWhere('first_name_en', 'like', "%{$this->search}%")
                        ->orWhere('last_name_en', 'like', "%{$this->search}%");

                    if ($digits !== '') {
                        $query->orWhere('id', (int) $digits);
                    }
                });
            })
            ->when($this->positionId !== '', fn ($query) => $query->where('position_id', (int) $this->positionId))
            ->when($this->gender !== '', fn ($query) => $query->where('gender', $this->gender))
            ->when($this->degreeLevel !== '', fn ($query) => $query->whereHas('education', fn ($q) => $q->where('degree_level', $this->degreeLevel)))
            ->when($this->minGpa !== '', fn ($query) => $query->whereHas('education', fn ($q) => $q->where('gpa', '>=', (float) $this->minGpa)))
            ->when($this->yearApplied !== '', fn ($query) => $query->whereYear('created_at', $this->yearApplied))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function positionSummary()
    {
        return Position::query()
            ->when($this->posSearch !== '', fn ($query) => $query->where('title', 'like', "%{$this->posSearch}%"))
            ->withCount([
                'applicants as total_count' => fn ($query) => $query->where('status', '!=', 'draft')
                    ->when($this->month !== '', fn ($q) => $q->whereMonth('created_at', $this->month))
                    ->when($this->year !== '', fn ($q) => $q->whereYear('created_at', $this->year)),
                'applicants as qualified_count' => fn ($query) => $query->whereIn('status', ['qualified', 'code_generated', 'awaiting_review', 'passed_test', 'failed_test'])
                    ->when($this->month !== '', fn ($q) => $q->whereMonth('created_at', $this->month))
                    ->when($this->year !== '', fn ($q) => $q->whereYear('created_at', $this->year)),
                'applicants as passed_count' => fn ($query) => $query->where('status', 'passed_test')
                    ->when($this->month !== '', fn ($q) => $q->whereMonth('created_at', $this->month))
                    ->when($this->year !== '', fn ($q) => $q->whereYear('created_at', $this->year)),
            ])
            ->get()
            ->filter(fn ($position) => $position->total_count > 0)
            ->sortByDesc('total_count')
            ->values();
    }

    #[Computed]
    public function positionTotals(): array
    {
        $rows = $this->positionSummary;

        return [
            'positions' => $rows->count(),
            'total' => $rows->sum('total_count'),
            'qualified' => $rows->sum('qualified_count'),
            'passed' => $rows->sum('passed_count'),
        ];
    }

    public function pct(int $value, int $total): int
    {
        return $total > 0 ? (int) round($value / $total * 100) : 0;
    }

    protected function levelRank(string $level): int
    {
        return match ($level) {
            'master' => 6,
            'bachelor' => 5,
            'vocational' => 4,
            'diploma_high_vocational' => 3,
            'senior_high_vocational_cert' => 2,
            'junior_high' => 1,
            default => 0,
        };
    }

    public function topEducation(Applicant $applicant): ?ApplicantEducation
    {
        return $applicant->education->sortByDesc(fn ($e) => $this->levelRank($e->degree_level))->first();
    }

    public function genderLabel(?string $gender): string
    {
        return match ($gender) {
            'male' => 'ชาย',
            'female' => 'หญิง',
            'other' => 'อื่นๆ',
            default => '-',
        };
    }

    public function degreeLevelLabel(?string $level): string
    {
        return match ($level) {
            'master' => 'ป.โท',
            'bachelor' => 'ป.ตรี',
            'vocational' => 'อาชีวะ',
            'diploma_high_vocational' => 'ปวส.',
            'senior_high_vocational_cert' => 'ม.ปลาย/ปวช.',
            'junior_high' => 'ม.ต้น',
            'other' => 'อื่นๆ',
            default => '-',
        };
    }

    public function statusMeta(string $status): array
    {
        return match ($status) {
            'submitted' => ['label' => 'ส่งใบสมัคร', 'class' => 'bg-stone-100 text-stone-600'],
            'qualified' => ['label' => 'ผ่านเกณฑ์', 'class' => 'bg-amber-50 text-amber-700'],
            'disqualified' => ['label' => 'ไม่ผ่านเกณฑ์', 'class' => 'bg-red-50 text-red-700'],
            'code_generated' => ['label' => 'สร้างรหัสแล้ว', 'class' => 'bg-blue-50 text-blue-700'],
            'awaiting_review' => ['label' => 'รอตรวจข้อสอบ', 'class' => 'bg-amber-50 text-amber-700'],
            'passed_test' => ['label' => 'ผ่านการทดสอบ', 'class' => 'bg-green-50 text-green-700'],
            'failed_test' => ['label' => 'ไม่ผ่านการทดสอบ', 'class' => 'bg-red-50 text-red-700'],
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

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="flex min-h-screen bg-[#FAF8F3]">
    <x-admin.sidebar />

    <main class="min-w-0 flex-1 p-6 lg:p-10">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-stone-900">สรุปผล</h1>
                <p class="mt-1 text-sm text-stone-500">ภาพรวมผลการคัดเลือกผู้สมัคร</p>
            </div>
            <span title="ยังไม่เปิดใช้งาน" class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-stone-200 px-4 py-2 text-sm font-medium text-stone-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                ส่งออก PDF
            </span>
        </div>

        <div class="mt-5 flex gap-1 border-b border-stone-200">
            <button wire:click="setTab('all')" type="button" class="border-b-2 px-1 pb-3 text-sm font-medium {{ $tab === 'all' ? 'border-[#BA772A] text-[#BA772A]' : 'border-transparent text-stone-500 hover:text-stone-700' }}">
                ดูทุกคนทั้งหมด
            </button>
            <button wire:click="setTab('byPosition')" type="button" class="ml-4 border-b-2 px-1 pb-3 text-sm font-medium {{ $tab === 'byPosition' ? 'border-[#BA772A] text-[#BA772A]' : 'border-transparent text-stone-500 hover:text-stone-700' }}">
                ดูจากตำแหน่งงาน
            </button>
        </div>

        @if ($tab === 'all')
            <div class="mt-4">
                <label class="text-sm text-stone-500">ค้นหาชื่อ-นามสกุล หรือรหัสผู้สมัคร</label>
                <div class="relative mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" wire:model="search" placeholder="เช่น สมชาย หรือ APP-000013"
                           class="w-full rounded-lg border-stone-300 py-2.5 pl-9 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div>
                    <label class="text-xs text-stone-500">ตำแหน่ง</label>
                    <select wire:model="positionId" class="mt-1 w-full rounded-lg border-stone-300 py-2 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                        <option value="">ทั้งหมด</option>
                        @foreach ($this->positions as $position)
                            <option value="{{ $position->id }}">{{ $position->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-stone-500">เพศ</label>
                    <select wire:model="gender" class="mt-1 w-full rounded-lg border-stone-300 py-2 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                        <option value="">ทั้งหมด</option>
                        <option value="male">ชาย</option>
                        <option value="female">หญิง</option>
                        <option value="other">อื่นๆ</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-stone-500">วุฒิ</label>
                    <select wire:model="degreeLevel" class="mt-1 w-full rounded-lg border-stone-300 py-2 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                        <option value="">ทั้งหมด</option>
                        <option value="master">ป.โท</option>
                        <option value="bachelor">ป.ตรี</option>
                        <option value="vocational">อาชีวะ</option>
                        <option value="diploma_high_vocational">ปวส.</option>
                        <option value="senior_high_vocational_cert">ม.ปลาย/ปวช.</option>
                        <option value="junior_high">ม.ต้น</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-stone-500">เกรดขั้นต่ำ</label>
                    <select wire:model="minGpa" class="mt-1 w-full rounded-lg border-stone-300 py-2 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                        <option value="">ทั้งหมด</option>
                        <option value="3.50">3.50+</option>
                        <option value="3.00">3.00+</option>
                        <option value="2.50">2.50+</option>
                        <option value="2.00">2.00+</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-stone-500">ปีที่สมัคร</label>
                    <select wire:model="yearApplied" class="mt-1 w-full rounded-lg border-stone-300 py-2 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                        <option value="">ทั้งหมด</option>
                        @foreach ($this->appliedYears as $yr)
                            <option value="{{ $yr }}">{{ $yr + 543 }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-end gap-4">
                <button wire:click="clearFilters" type="button" class="text-sm text-stone-500 hover:text-stone-700">ล้างตัวกรอง</button>
                <button wire:click="search_" type="button" class="rounded-lg bg-[#BA772A] px-5 py-2 text-sm font-semibold text-white hover:bg-[#a6661f]">ค้นหา</button>
            </div>

            <p class="mt-4 text-sm text-stone-500">
                แสดง {{ $this->applicants->firstItem() ?? 0 }}-{{ $this->applicants->lastItem() ?? 0 }} จาก {{ $this->applicants->total() }} รายการ
            </p>

            <div class="mt-3 overflow-x-auto rounded-xl border border-stone-200 bg-white">
                <table class="w-full min-w-[1100px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-stone-500">
                            <th class="px-4 py-3 font-medium">วันที่สมัคร</th>
                            <th class="px-4 py-3 font-medium">ช่องทางที่รับสมัคร</th>
                            <th class="px-4 py-3 font-medium">เพศ</th>
                            <th class="px-4 py-3 font-medium">ชื่อ-นามสกุล</th>
                            <th class="px-4 py-3 font-medium">ตำแหน่ง</th>
                            <th class="px-4 py-3 font-medium">เกรด</th>
                            <th class="px-4 py-3 font-medium">วุฒิ</th>
                            <th class="px-4 py-3 font-medium">คณะ/สาขา</th>
                            <th class="px-4 py-3 font-medium">สถาบันที่จบ</th>
                            <th class="px-4 py-3 font-medium">สถานะ</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($this->applicants as $applicant)
                            @php
                                $edu = $this->topEducation($applicant);
                                $meta = $this->statusMeta($applicant->status);
                            @endphp
                            <tr class="text-stone-700">
                                <td class="whitespace-nowrap px-4 py-3">{{ $this->thaiDate($applicant->created_at) }}</td>
                                <td class="px-4 py-3">{{ implode(', ', (array) $applicant->source_channels) ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $this->genderLabel($applicant->gender) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-stone-900">{{ $applicant->title_th }}{{ $applicant->first_name_th }} {{ $applicant->last_name_th }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $applicant->position->title ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $edu?->gpa ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $this->degreeLevelLabel($edu?->degree_level) }}</td>
                                <td class="px-4 py-3">{{ $edu?->major ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $edu?->institution_name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $meta['class'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ $meta['label'] }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a href="{{ route('admin.applicants.show', $applicant) }}" wire:navigate
                                       class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 hover:border-[#BA772A] hover:text-[#BA772A]">
                                        รายละเอียด
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-5 py-10 text-center text-stone-400">ไม่พบข้อมูลที่ตรงกับเงื่อนไข</td>
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
        @else
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" wire:model.live="posSearch" placeholder="ค้นหาตำแหน่ง"
                           class="w-full rounded-lg border-stone-300 py-2.5 pl-9 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                </div>
                <select wire:model.live="month" class="rounded-lg border-stone-300 py-2.5 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    <option value="">เดือน: ทั้งหมด</option>
                    @foreach (['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'] as $i => $name)
                        <option value="{{ $i + 1 }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="year" class="rounded-lg border-stone-300 py-2.5 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                    <option value="">ปี: ทั้งหมด</option>
                    @foreach ($this->appliedYears as $yr)
                        <option value="{{ $yr }}">{{ $yr + 543 }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-2 flex justify-end">
                <button wire:click="clearPositionFilters" type="button" class="text-sm text-stone-500 hover:text-stone-700">ล้างตัวกรอง</button>
            </div>

            <p class="mt-4 text-sm font-medium text-stone-600">
                {{ $this->positionTotals['positions'] }} ตำแหน่ง &middot; รวม {{ $this->positionTotals['total'] }} ใบสมัคร
            </p>

            <div class="mt-3 overflow-x-auto rounded-xl border border-stone-200 bg-white">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-stone-500">
                            <th class="px-4 py-3 font-medium">ลำดับ</th>
                            <th class="px-4 py-3 font-medium">ตำแหน่ง</th>
                            <th class="bg-amber-50/60 px-4 py-3 font-medium text-amber-700">ใบสมัคร</th>
                            <th class="bg-amber-50/60 px-4 py-3 font-medium text-amber-700">%</th>
                            <th class="bg-blue-50/60 px-4 py-3 font-medium text-blue-700">ผ่านคุณสมบัติ</th>
                            <th class="bg-blue-50/60 px-4 py-3 font-medium text-blue-700">%</th>
                            <th class="bg-green-50/60 px-4 py-3 font-medium text-green-700">ทำแบบทดสอบผ่าน</th>
                            <th class="bg-green-50/60 px-4 py-3 font-medium text-green-700">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($this->positionSummary as $index => $row)
                            <tr class="text-stone-700">
                                <td class="px-4 py-3 text-stone-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-stone-900">{{ $row->title }}</td>
                                <td class="bg-amber-50/30 px-4 py-3">{{ $row->total_count }}</td>
                                <td class="bg-amber-50/30 px-4 py-3">{{ $this->pct($row->total_count, $this->positionTotals['total']) }}%</td>
                                <td class="bg-blue-50/30 px-4 py-3">{{ $row->qualified_count }}</td>
                                <td class="bg-blue-50/30 px-4 py-3">{{ $this->pct($row->qualified_count, $this->positionTotals['qualified']) }}%</td>
                                <td class="bg-green-50/30 px-4 py-3">{{ $row->passed_count }}</td>
                                <td class="bg-green-50/30 px-4 py-3">{{ $this->pct($row->passed_count, $this->positionTotals['passed']) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-stone-400">ไม่พบข้อมูลที่ตรงกับเงื่อนไข</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </main>
</div>
