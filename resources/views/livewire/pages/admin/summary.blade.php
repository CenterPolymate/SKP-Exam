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
        // ดึงปีด้วย PHP แทนการใช้ raw SQL เฉพาะฐานข้อมูล (YEAR()/EXTRACT()/strftime() ต่างกันไปแต่ละตัว)
        // เพื่อให้พอร์ทข้ามฐานข้อมูลได้โดยไม่ต้องเขียนเงื่อนไขเพิ่มทุกครั้ง
        return Applicant::pluck('created_at')
            ->map(fn ($date) => $date->year)
            ->unique()
            ->sortDesc()
            ->values();
    }

    protected function filteredApplicantsQuery()
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
            ->orderByDesc('created_at');
    }

    #[Computed]
    public function applicants()
    {
        return $this->filteredApplicantsQuery()->paginate($this->perPage);
    }

    // คอลัมน์ export ครอบคลุมทุกช่องที่ผู้สมัครกรอกในฟอร์มสมัคร (ไม่ใช่แค่คอลัมน์ที่โชว์ในตารางสรุป)
    // คืนค่าเป็น [label => closure(Applicant $a): mixed] เพื่อให้ label กับค่าผูกคู่กันเสมอ ไม่หลุด sync กัน
    protected function exportColumns(): array
    {
        $yesNo = fn ($v) => is_null($v) ? '-' : ($v ? 'ใช่' : 'ไม่ใช่');
        $aliveLabel = fn ($v) => is_null($v) ? '-' : ($v ? 'มีชีวิตอยู่' : 'เสียชีวิตแล้ว');
        $fmtDate = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : '-';
        $tier = fn ($v) => is_null($v) ? '-' : ($v >= 4 ? 'ดีมาก' : ($v >= 2 ? 'ดี' : 'พอใช้'));

        $genderLabels = ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ'];
        $livingStatusLabels = ['own_home' => 'บ้านส่วนตัว', 'rent_home' => 'บ้านเช่า', 'with_parents' => 'อาศัยบิดามารดา', 'with_other' => 'อาศัยอยู่กับผู้อื่น'];
        $maritalStatusLabels = ['single' => 'โสด', 'married' => 'แต่งงาน', 'divorced' => 'หย่า', 'widowed' => 'หม้าย', 'separated' => 'แยกกันอยู่'];
        $militaryStatusLabels = ['exempted' => 'ได้รับการยกเว้น', 'completed' => 'ผ่านการเกณฑ์ทหารแล้ว', 'awaiting_conscription' => 'รอเข้ารับการเกณฑ์ทหาร'];
        $degreeLevelLabels = ['junior_high' => 'มัธยมศึกษาตอนต้น', 'senior_high_vocational_cert' => 'มัธยมศึกษาตอนปลาย/ปวช.', 'diploma_high_vocational' => 'อนุปริญญา/ปวส.', 'vocational' => 'วิชาชีพ', 'bachelor' => 'ปริญญาตรี', 'master' => 'ปริญญาโท', 'other' => 'อื่นๆ'];
        $languageLabels = ['thai' => 'ไทย', 'english' => 'อังกฤษ', 'chinese' => 'จีน', 'other' => 'อื่นๆ'];
        $documentTypeLabels = [
            'photo' => 'รูปถ่ายหน้าตรง ขนาด 1 นิ้ว', 'education_certificate' => 'ใบรับรองการศึกษา',
            'house_registration' => 'สำเนาทะเบียนบ้าน', 'id_card_copy' => 'สำเนาบัตรประชาชน',
            'military_certificate' => 'ใบผ่านทหาร', 'work_certificate_copy' => 'สำเนาใบผ่านงาน',
            'driving_license' => 'สำเนาใบขับขี่', 'other' => 'เอกสารอื่นๆ',
        ];

        return [
            // ข้อมูลใบสมัคร
            'วันที่สมัคร' => fn ($a) => $this->thaiDate($a->created_at),
            'รหัสผู้สมัคร' => fn ($a) => 'APP-'.str_pad($a->id, 6, '0', STR_PAD_LEFT),
            'สถานะ' => fn ($a) => $this->statusMeta($a->status)['label'],
            'ตำแหน่งที่สมัคร' => fn ($a) => $a->position->title ?? '-',
            'เงินเดือนที่ต้องการ' => fn ($a) => number_format((float) $a->expected_salary, 2),
            'ช่องทางที่ทราบข่าว' => fn ($a) => implode(', ', (array) $a->source_channels) ?: '-',
            'ระบุช่องทางอื่นๆ' => fn ($a) => $a->source_channel_other ?: '-',
            'ชื่อเพื่อน/ญาติที่แนะนำ' => fn ($a) => $a->referred_friend_name ?: '-',

            // ชื่อ-นามสกุล
            'คำนำหน้า (TH)' => fn ($a) => $a->title_th,
            'ชื่อ (TH)' => fn ($a) => $a->first_name_th,
            'นามสกุล (TH)' => fn ($a) => $a->last_name_th,
            'ชื่อเล่น (TH)' => fn ($a) => $a->nickname_th,
            'Title (EN)' => fn ($a) => $a->title_en,
            'Name (EN)' => fn ($a) => $a->first_name_en,
            'Surname (EN)' => fn ($a) => $a->last_name_en,
            'Nickname (EN)' => fn ($a) => $a->nickname_en,

            // ข้อมูลส่วนตัว
            'วันเกิด' => fn ($a) => $fmtDate($a->date_of_birth),
            'อายุ' => fn ($a) => $a->date_of_birth ? \Illuminate\Support\Carbon::parse($a->date_of_birth)->age : '-',
            'สัญชาติ' => fn ($a) => $a->nationality,
            'เชื้อชาติ' => fn ($a) => $a->ethnicity,
            'ศาสนา' => fn ($a) => $a->religion,
            'เพศ' => fn ($a) => $genderLabels[$a->gender] ?? '-',
            'ส่วนสูง (ซม.)' => fn ($a) => $a->height_cm,
            'น้ำหนัก (กก.)' => fn ($a) => $a->weight_kg,
            'มีโรคประจำตัว' => fn ($a) => $yesNo($a->has_congenital_disease),
            'รายละเอียดโรคประจำตัว' => fn ($a) => $a->congenital_disease_detail ?: '-',
            'มีประวัติอาชญากรรม' => fn ($a) => $yesNo($a->has_criminal_record),
            'รายละเอียดประวัติอาชญากรรม' => fn ($a) => $a->criminal_record_detail ?: '-',
            'มีประวัติหมิ่นพระมหากษัตริย์' => fn ($a) => $yesNo($a->has_lese_majeste_record),
            'รายละเอียด (หมิ่นพระมหากษัตริย์)' => fn ($a) => $a->lese_majeste_detail ?: '-',

            // ที่อยู่
            'ประเทศ' => fn ($a) => $a->country,
            'ภูมิลำเนา' => fn ($a) => $a->hometown,
            'ที่อยู่ปัจจุบัน' => fn ($a) => $a->current_address,

            // ติดต่อ
            'เบอร์โทรศัพท์' => fn ($a) => $a->phone,
            'Email' => fn ($a) => $a->email,
            'Line ID' => fn ($a) => $a->line_id ?: '-',
            'Facebook' => fn ($a) => $a->facebook ?: '-',
            'Instagram' => fn ($a) => $a->instagram ?: '-',
            'Twitter' => fn ($a) => $a->twitter ?: '-',

            // บัตรประชาชน
            'เลขบัตรประชาชน' => fn ($a) => $a->national_id_number,
            'เลขหลังบัตรประชาชน' => fn ($a) => $a->national_id_back_number ?: '-',
            'วันออกบัตร' => fn ($a) => $fmtDate($a->id_issue_date),
            'วันหมดอายุบัตร' => fn ($a) => $fmtDate($a->id_expiry_date),

            // ครอบครัว
            'สถานะความเป็นอยู่' => fn ($a) => $livingStatusLabels[$a->living_status] ?? '-',
            'สถานะครอบครัว' => fn ($a) => $maritalStatusLabels[$a->marital_status] ?? '-',
            'จดทะเบียนสมรส' => fn ($a) => $yesNo($a->marriage_registered),
            'ชื่อคู่สมรส' => fn ($a) => $a->spouse_name ?: '-',
            'อาชีพคู่สมรส' => fn ($a) => $a->spouse_occupation ?: '-',
            'จำนวนบุตร' => fn ($a) => $a->children_count ?? '-',

            // บิดามารดา
            'ชื่อบิดา' => fn ($a) => $a->father_name ?: '-',
            'อายุบิดา' => fn ($a) => $a->father_age ?? '-',
            'อาชีพบิดา' => fn ($a) => $a->father_occupation ?: '-',
            'สถานะบิดา' => fn ($a) => $aliveLabel($a->father_alive),
            'ชื่อมารดา' => fn ($a) => $a->mother_name ?: '-',
            'อายุมารดา' => fn ($a) => $a->mother_age ?? '-',
            'อาชีพมารดา' => fn ($a) => $a->mother_occupation ?: '-',
            'สถานะมารดา' => fn ($a) => $aliveLabel($a->mother_alive),
            'จำนวนพี่น้อง' => fn ($a) => $a->siblings_count ?? '-',
            'เป็นบุตรคนที่' => fn ($a) => $a->birth_order ?? '-',

            // ทหาร
            'สถานะภาพทางการทหาร' => fn ($a) => $militaryStatusLabels[$a->military_status] ?? '-',

            // การศึกษา (รวมทุกระดับที่กรอกไว้)
            'ประวัติการศึกษา' => fn ($a) => $a->education->map(fn ($e) => trim(
                ($degreeLevelLabels[$e->degree_level] ?? $e->degree_level).' - '.$e->institution_name
                .($e->major ? ' ('.$e->major.')' : '').($e->gpa ? ' GPA '.$e->gpa : '')
                .' '.$e->start_year.'-'.$e->end_year
            ))->implode(' | ') ?: '-',

            // ภาษา
            'ความสามารถด้านภาษา' => fn ($a) => $a->languages->map(fn ($l) => (
                ($l->language === 'other' ? $l->custom_language_name : ($languageLabels[$l->language] ?? $l->language))
                .' (พูด '.$tier($l->speaking_level).', ฟัง '.$tier($l->understanding_level)
                .', อ่าน '.$tier($l->reading_level).', เขียน '.$tier($l->writing_level).')'
            ))->implode(' | ') ?: '-',
            'ใบรับรองด้านภาษา' => fn ($a) => $a->language_certificate_note ?: '-',

            // ประวัติการทำงาน
            'ประวัติการทำงาน' => fn ($a) => $a->workHistories->map(fn ($w) => trim(
                $w->company_name.' - '.$w->position.' ('.$w->start_year.'-'.$w->end_year.')'
                .($w->last_salary ? ' เงินเดือนล่าสุด '.number_format((float) $w->last_salary, 2).' บาท' : '')
            ))->implode(' | ') ?: '-',

            // บุคคลอ้างอิง
            'บุคคลอ้างอิง 1' => function ($a) {
                $ref = $a->references->firstWhere('order_no', 1);

                return $ref ? "{$ref->name} ({$ref->relationship}) โทร {$ref->phone}" : '-';
            },
            'บุคคลอ้างอิง 2' => function ($a) {
                $ref = $a->references->firstWhere('order_no', 2);

                return $ref ? "{$ref->name} ({$ref->relationship}) โทร {$ref->phone}" : '-';
            },

            // อื่นๆ
            'ความรู้ความสามารถพิเศษ' => fn ($a) => $a->special_skills ?: '-',
            'สามารถหาผู้ค้ำประกันได้' => fn ($a) => $yesNo($a->guarantor_required_ok),
            'ทำงานเป็นกะหมุนเวียนได้' => fn ($a) => $yesNo($a->shift_work_ok),
            'หนี้นอกระบบ (บาท/เดือน)' => fn ($a) => $a->informal_debt_amount ?? '-',
            'ดอกเบี้ย (ร้อยละ/เดือน)' => fn ($a) => $a->informal_debt_interest_rate ?? '-',
            'วงเงินบัตรเครดิต/บัตรกดเงินสด (บาท)' => fn ($a) => $a->credit_card_debt_amount ?? '-',
            'ยอดค้างชำระ (บาท)' => fn ($a) => $a->total_outstanding_balance ?? '-',
            'ประวัติเจ็บป่วย/อุบัติเหตุ (3 ปีย้อนหลัง)' => fn ($a) => $a->illness_or_accident_history ?: '-',
            'บุคคลในบริษัทที่รู้จัก' => fn ($a) => $a->known_person_in_company ?: '-',
            'ความสัมพันธ์ (บุคคลที่รู้จัก)' => fn ($a) => $a->known_person_relation ?: '-',
            'วันที่พร้อมเริ่มงาน' => fn ($a) => $fmtDate($a->available_start_date),

            // เอกสารแนบ
            'เอกสารที่แนบ' => fn ($a) => $a->documents->map(fn ($d) => $documentTypeLabels[$d->document_type] ?? $d->document_type)->implode(', ') ?: '-',

            // ยินยอม/ลงชื่อ
            'ยินยอมรับรองความถูกต้องของข้อมูล' => fn ($a) => $yesNo($a->data_accuracy_consent),
            'ยินยอมตามนโยบายความเป็นส่วนตัว' => fn ($a) => $yesNo($a->privacy_policy_consent),
            'ลงชื่อผู้สมัครงาน' => fn ($a) => $a->signature_name ?: '-',
            'วันที่ลงชื่อ' => fn ($a) => $fmtDate($a->signed_at),

            // การตรวจสอบของ HR
            'ตรวจสอบ Blacklist (HR)' => fn ($a) => $yesNo($a->hr_blacklist_checked),
            'ตรวจสอบประวัติอาชญากรรม (HR)' => fn ($a) => $yesNo($a->hr_criminal_checked),
            'ตรวจสอบ Social Media (HR)' => fn ($a) => $yesNo($a->hr_social_checked),
            'หมายเหตุ HR' => fn ($a) => $a->hr_verification_note ?: '-',
            'ผู้ตรวจสอบ' => fn ($a) => $a->reviewer->name ?? '-',
            'วันที่ตรวจสอบ' => fn ($a) => $a->reviewed_at ? $a->reviewed_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function exportExcel()
    {
        $applicants = $this->filteredApplicantsQuery()
            ->with(['languages', 'workHistories', 'references', 'documents', 'reviewer'])
            ->get();

        $columns = $this->exportColumns();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray(array_keys($columns), null, 'A1');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($applicants as $applicant) {
            $sheet->fromArray(array_map(fn ($resolve) => $resolve($applicant), array_values($columns)), null, "A{$row}");
            $row++;
        }

        foreach (range(1, count($columns)) as $colIndex) {
            $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);
        }

        $filename = 'ข้อมูลผู้สมัคร-'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
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
            <button
                type="button"
                wire:click="exportExcel"
                wire:loading.attr="disabled"
                wire:target="exportExcel"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 disabled:opacity-60"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span wire:loading.remove wire:target="exportExcel">ส่งออก Excel</span>
                <span wire:loading wire:target="exportExcel">กำลังสร้างไฟล์...</span>
            </button>
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
                                    <a href="{{ route('admin.summary.applicants.show', $applicant) }}" wire:navigate
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
