<?php

use App\Livewire\Actions\Logout;
use App\Models\Applicant;
use App\Models\Exam;
use App\Models\ExamAssignment;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    public ?int $generatingForId = null;

    public array $selectedExamIds = [];

    public string $examDeadline = '';

    public ?array $generatedResult = null;

    public function search_(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'positionId');
        $this->resetPage();
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
            ->whereIn('status', ['qualified', 'code_generated'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name_th', 'like', "%{$this->search}%")
                        ->orWhere('last_name_th', 'like', "%{$this->search}%")
                        ->orWhere('first_name_en', 'like', "%{$this->search}%")
                        ->orWhere('last_name_en', 'like', "%{$this->search}%");
                });
            })
            ->when($this->positionId !== '', fn ($query) => $query->where('position_id', (int) $this->positionId))
            ->orderByDesc('reviewed_at')
            ->paginate($this->perPage);
    }

    public function accountStatusMeta(string $status): array
    {
        return match ($status) {
            'qualified' => ['label' => 'ผ่านเกณฑ์', 'class' => 'bg-amber-50 text-amber-700'],
            'code_generated' => ['label' => 'สร้างรหัสแล้ว', 'class' => 'bg-blue-50 text-blue-700'],
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

    protected function thaiShortDate(?\Illuminate\Support\Carbon $date): string
    {
        if (! $date) {
            return '-';
        }

        return sprintf('%02d/%02d/%d', $date->day, $date->month, $date->year + 543);
    }

    public function generatingApplicant(): ?Applicant
    {
        return $this->generatingForId ? Applicant::with('position')->find($this->generatingForId) : null;
    }

    public function mandatoryExamsFor(Applicant $applicant)
    {
        return Exam::where('status', 'published')
            ->where('target_type', 'mandatory')
            ->orderBy('name')
            ->get();
    }

    public function optionalExamsFor(Applicant $applicant)
    {
        return Exam::where('status', 'published')
            ->where('target_type', 'position_specific')
            ->whereHas('positions', fn ($q) => $q->where('positions.id', $applicant->position_id))
            ->orderBy('name')
            ->get();
    }

    public function openGenerate(int $applicantId): void
    {
        $applicant = Applicant::with('position')->findOrFail($applicantId);

        $this->generatingForId = $applicantId;
        $this->examDeadline = '';
        $this->generatedResult = null;
        $this->resetValidation();
        $this->selectedExamIds = $this->optionalExamsFor($applicant)->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function closeGenerate(): void
    {
        $this->reset('generatingForId', 'selectedExamIds', 'examDeadline', 'generatedResult');
    }

    public function finishGenerate(): void
    {
        $this->reset('generatingForId', 'selectedExamIds', 'examDeadline', 'generatedResult');
    }

    public function confirmGenerate(): void
    {
        $this->validate([
            'examDeadline' => ['required', 'date', 'after_or_equal:today'],
        ], [
            'examDeadline.required' => 'กรุณาเลือกวันสิ้นสุดของการสอบ',
            'examDeadline.after_or_equal' => 'วันสิ้นสุดต้องไม่ใช่วันในอดีต',
        ]);

        $applicant = Applicant::findOrFail($this->generatingForId);

        $mandatoryIds = $this->mandatoryExamsFor($applicant)->pluck('id')->all();
        $optionalIds = $this->optionalExamsFor($applicant)->pluck('id')->all();
        $chosenOptionalIds = array_values(array_intersect($optionalIds, array_map('intval', $this->selectedExamIds)));
        $examIds = array_values(array_unique(array_merge($mandatoryIds, $chosenOptionalIds)));

        if (empty($examIds)) {
            $this->addError('selectedExamIds', 'ไม่มีชุดข้อสอบให้มอบหมาย กรุณาสร้าง/เผยแพร่ข้อสอบในคลังข้อสอบก่อน');

            return;
        }

        $username = null;
        for ($i = 0; $i < 20; $i++) {
            $candidate = 'exam_'.random_int(1000, 9999);
            if (! ExamAssignment::where('username', $candidate)->exists()) {
                $username = $candidate;
                break;
            }
        }
        $username ??= 'exam_'.Str::random(8);

        $password = Str::password(10, symbols: false);
        $exams = Exam::whereIn('id', $examIds)->get();

        $assignment = DB::transaction(function () use ($applicant, $username, $password, $examIds) {
            $assignment = ExamAssignment::create([
                'applicant_id' => $applicant->id,
                'username' => $username,
                'password_hash' => Hash::make($password),
                'exam_deadline' => $this->examDeadline,
                'created_by' => auth()->id(),
            ]);

            foreach ($examIds as $examId) {
                $assignment->attempts()->create([
                    'exam_id' => $examId,
                    'status' => 'not_started',
                ]);
            }

            $applicant->update(['status' => 'code_generated']);

            return $assignment;
        });

        $link = url('/exam/login');
        $totalMinutes = (int) $exams->sum('duration_minutes');
        $duration = sprintf('%d:%02d:00', intdiv($totalMinutes, 60), $totalMinutes % 60);
        $deadlineFormatted = $this->thaiShortDate($assignment->exam_deadline);

        $message = "สวัสดีค่ะ คุณ{$applicant->first_name_th} {$applicant->last_name_th}\n"
            ."ทาง SK Polymer ได้พิจารณาใบสมัครของคุณแล้ว\n"
            ."ขอเชิญทำแบบทดสอบออนไลน์ตามรายละเอียดนี้ค่ะ\n\n"
            ."ลิงก์เข้าสอบ: {$link}\n"
            ."ชื่อผู้ใช้: {$username}\n"
            ."รหัสผ่าน: {$password}\n"
            ."กำหนดถึง: {$deadlineFormatted}\n"
            ."ใช้เวลา {$duration} ({$exams->count()} ชุด)";

        $this->generatedResult = [
            'username' => $username,
            'password' => $password,
            'link' => $link,
            'message' => $message,
        ];
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
        <h1 class="text-2xl font-bold text-stone-900">จัดการบัญชีผู้สมัคร</h1>
        <p class="mt-1 text-sm text-stone-500">สร้างรหัสเข้าสอบให้ผู้สมัครที่ผ่านคุณสมบัติแล้ว</p>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-[1fr_260px]">
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

        <p class="mt-4 text-sm text-stone-500">
            แสดง {{ $this->applicants->firstItem() ?? 0 }}-{{ $this->applicants->lastItem() ?? 0 }} จาก {{ $this->applicants->total() }} รายการ
        </p>

        <div class="mt-3 overflow-x-auto rounded-xl border border-stone-200 bg-white">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50 text-stone-500">
                        <th class="px-5 py-3 font-medium">ชื่อ-นามสกุล</th>
                        <th class="px-5 py-3 font-medium">ตำแหน่งที่ต้องการ</th>
                        <th class="px-5 py-3 font-medium">ผ่านคุณสมบัติ</th>
                        <th class="px-5 py-3 font-medium">สถานะบัญชี</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($this->applicants as $applicant)
                        @php $status = $this->accountStatusMeta($applicant->status); @endphp
                        <tr class="text-stone-700">
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $applicant->title_th }}{{ $applicant->first_name_th }} {{ $applicant->last_name_th }}</td>
                            <td class="px-5 py-3">{{ $applicant->position->title ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $this->thaiDate($applicant->reviewed_at) }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $status['class'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if ($applicant->status === 'code_generated')
                                    <a href="{{ route('admin.applicants.show', $applicant) }}" wire:navigate
                                       class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-medium text-stone-600 hover:border-[#BA772A] hover:text-[#BA772A]">
                                        ดูรายละเอียด
                                    </a>
                                @else
                                    <button wire:click="openGenerate({{ $applicant->id }})" type="button"
                                            class="rounded-lg border border-[#BA772A] px-3 py-1.5 text-xs font-medium text-[#BA772A] hover:bg-amber-50">
                                        สร้างรหัสเข้าสอบ
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-stone-400">ยังไม่มีผู้สมัครที่ผ่านคุณสมบัติ</td>
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

    {{-- Generate exam code modal --}}
    @if ($generatingForId)
        @php
            $genApplicant = $this->generatingApplicant();
            $mandatory = $genApplicant ? $this->mandatoryExamsFor($genApplicant) : collect();
            $optional = $genApplicant ? $this->optionalExamsFor($genApplicant) : collect();
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 px-4 py-8">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                @if (! $generatedResult)
                    <h3 class="text-lg font-semibold text-stone-900">สร้างรหัสเข้าสอบ</h3>
                    <p class="mt-1 text-sm text-stone-500">
                        {{ $genApplicant->title_th }}{{ $genApplicant->first_name_th }} {{ $genApplicant->last_name_th }}
                        &middot; {{ $genApplicant->position->title ?? '-' }}
                    </p>

                    @if ($mandatory->isEmpty() && $optional->isEmpty())
                        <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-700">
                            ยังไม่มีข้อสอบที่เผยแพร่แล้วสำหรับตำแหน่งนี้ กรุณาสร้าง/เผยแพร่ข้อสอบในคลังข้อสอบก่อน
                        </p>
                    @else
                        @if ($mandatory->isNotEmpty())
                            <div class="mt-4">
                                <label class="text-sm text-stone-500">ชุดข้อสอบบังคับ (ทุกคนต้องทำ)</label>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @foreach ($mandatory as $exam)
                                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600">{{ $exam->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($optional->isNotEmpty())
                            <div class="mt-4">
                                <label class="text-sm text-stone-500">เลือกข้อสอบความถนัด (เฉพาะตำแหน่งนี้ {{ $genApplicant->position->title ?? '' }})</label>
                                <div class="mt-1 max-h-40 space-y-1 overflow-y-auto rounded-lg border border-stone-200 p-3">
                                    @foreach ($optional as $exam)
                                        <label class="flex items-center justify-between gap-2 text-sm text-stone-600">
                                            <span class="flex items-center gap-2">
                                                <input type="checkbox" wire:model="selectedExamIds" value="{{ $exam->id }}" class="rounded border-stone-300 text-[#BA772A] focus:ring-[#BA772A]">
                                                {{ $exam->name }}
                                            </span>
                                            <span class="shrink-0 text-xs text-stone-400">{{ $exam->duration_minutes }} นาที</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @error('selectedExamIds') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="mt-4">
                            <label class="text-sm text-stone-500">วันสิ้นสุดของการสอบ <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="examDeadline" class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]">
                            @error('examDeadline') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="closeGenerate" class="rounded-lg px-4 py-2 text-sm font-medium text-stone-500 hover:bg-stone-100">
                            ยกเลิก
                        </button>
                        @if ($mandatory->isNotEmpty() || $optional->isNotEmpty())
                            <button type="button" wire:click="confirmGenerate" wire:loading.attr="disabled" wire:target="confirmGenerate"
                                    class="rounded-lg bg-[#BA772A] px-5 py-2 text-sm font-semibold text-white hover:bg-[#a6661f] disabled:opacity-60">
                                ยืนยันการสร้างรหัส
                            </button>
                        @endif
                    </div>
                @else
                    {{-- Success screen --}}
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9 12l2 2 4-4"></path></svg>
                        <h3 class="text-lg font-semibold text-stone-900">สร้างรหัสเข้าสอบสำเร็จ</h3>
                    </div>
                    <p class="mt-1 text-sm text-stone-500">
                        สร้างบัญชีเข้าสอบให้ <span class="font-medium text-stone-700">{{ $genApplicant->title_th }}{{ $genApplicant->first_name_th }} {{ $genApplicant->last_name_th }}</span> แล้ว
                    </p>

                    <div class="mt-4 space-y-3" x-data="{ copied: null }">
                        <div class="flex items-center justify-between rounded-lg border border-stone-200 px-3 py-2">
                            <div>
                                <p class="text-xs text-stone-400">ชื่อผู้ใช้</p>
                                <p class="text-sm font-medium text-stone-800">{{ $generatedResult['username'] }}</p>
                            </div>
                            <button type="button" @click="navigator.clipboard.writeText('{{ $generatedResult['username'] }}'); copied = 'username'; setTimeout(() => copied = null, 1500)"
                                    class="shrink-0 rounded-md border border-stone-200 px-3 py-1 text-xs font-medium text-stone-600 hover:bg-stone-50">
                                <span x-text="copied === 'username' ? 'คัดลอกแล้ว' : 'คัดลอก'"></span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between rounded-lg border border-stone-200 px-3 py-2">
                            <div>
                                <p class="text-xs text-stone-400">รหัสผ่าน</p>
                                <p class="text-sm font-medium text-stone-800">{{ $generatedResult['password'] }}</p>
                            </div>
                            <button type="button" @click="navigator.clipboard.writeText('{{ $generatedResult['password'] }}'); copied = 'password'; setTimeout(() => copied = null, 1500)"
                                    class="shrink-0 rounded-md border border-stone-200 px-3 py-1 text-xs font-medium text-stone-600 hover:bg-stone-50">
                                <span x-text="copied === 'password' ? 'คัดลอกแล้ว' : 'คัดลอก'"></span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between gap-2 rounded-lg border border-stone-200 px-3 py-2">
                            <div class="min-w-0">
                                <p class="text-xs text-stone-400">ลิงก์เข้าสอบ</p>
                                <p class="truncate text-sm font-medium text-stone-800">{{ $generatedResult['link'] }}</p>
                            </div>
                            <button type="button" @click="navigator.clipboard.writeText('{{ $generatedResult['link'] }}'); copied = 'link'; setTimeout(() => copied = null, 1500)"
                                    class="shrink-0 rounded-md border border-stone-200 px-3 py-1 text-xs font-medium text-stone-600 hover:bg-stone-50">
                                <span x-text="copied === 'link' ? 'คัดลอกแล้ว' : 'คัดลอก'"></span>
                            </button>
                        </div>

                        <div>
                            <label class="text-sm text-stone-500">ข้อความพร้อมส่ง (แก้ไขได้)</label>
                            <textarea x-ref="messageBox" wire:model="generatedResult.message" rows="8"
                                      class="mt-1 w-full rounded-lg border-stone-300 text-sm text-stone-700 shadow-sm focus:border-[#BA772A] focus:ring-[#BA772A]"></textarea>
                        </div>

                        <button type="button" @click="navigator.clipboard.writeText($refs.messageBox.value); copied = 'message'; setTimeout(() => copied = null, 1500)"
                                class="w-full rounded-lg bg-[#BA772A] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#a6661f]">
                            <span x-text="copied === 'message' ? 'คัดลอกข้อความแล้ว' : 'คัดลอกข้อความทั้งหมด'"></span>
                        </button>

                        <p class="text-xs text-amber-700">⚠️ รหัสผ่านนี้จะไม่แสดงอีก กรุณาคัดลอกก่อนปิดหน้าต่าง</p>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button" wire:click="finishGenerate" class="rounded-lg px-5 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-100">
                            เสร็จสิ้น
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
