<?php

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantEducation;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantReference;
use App\Models\ApplicantWorkHistory;
use App\Models\Position;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.admin')] class extends Component
{
    use WithFileUploads;

    public Applicant $applicant;

    // ตำแหน่งงานที่สมัคร
    public $position_id = '';
    public $expected_salary = '';

    // ชื่อ-นามสกุล
    public $title_th = '';
    public $first_name_th = '';
    public $last_name_th = '';
    public $nickname_th = '';
    public $title_en = '';
    public $first_name_en = '';
    public $last_name_en = '';
    public $nickname_en = '';

    // ข้อมูลส่วนตัว
    public $date_of_birth = '';
    public $nationality = '';
    public $ethnicity = '';
    public $religion = '';
    public $gender = '';
    public $height_cm = '';
    public $weight_kg = '';
    public $has_congenital_disease = '';
    public $congenital_disease_detail = '';
    public $has_criminal_record = '';
    public $criminal_record_detail = '';
    public $country = '';
    public $hometown = '';

    // ติดต่อ
    public $email = '';
    public $line_id = '';
    public $phone = '';
    public $instagram = '';
    public $twitter = '';
    public $facebook = '';

    // บัตรประชาชน & ที่อยู่
    public $national_id_number = '';
    public $national_id_back_number = '';
    public $id_issue_date = '';
    public $id_expiry_date = '';
    public $current_address = '';

    // สถานะความเป็นอยู่ & ครอบครัว
    public $living_status = '';
    public $marital_status = '';
    public $marriage_registered = '';
    public $spouse_name = '';
    public $spouse_occupation = '';
    public $children_count = '';

    // บิดามารดาและพี่น้อง
    public $father_name = '';
    public $father_age = '';
    public $father_occupation = '';
    public $father_alive = '';
    public $mother_name = '';
    public $mother_age = '';
    public $mother_occupation = '';
    public $mother_alive = '';
    public $siblings_count = '';
    public $birth_order = '';

    // ทหาร & ประวัติ
    public $military_status = '';
    public $has_lese_majeste_record = '';
    public $lese_majeste_detail = '';

    // ประวัติการศึกษา
    public array $education = [];

    // ความสามารถด้านภาษา
    public array $languages = [];
    public $language_certificate_note = '';

    // ประวัติการทำงาน
    public array $workHistories = [];

    // ข้อมูลอื่นๆ
    public array $references = [];
    public $special_skills = '';
    public $guarantor_required_ok = '';
    public $shift_work_ok = '';
    public $informal_debt_amount = '';
    public $informal_debt_interest_rate = '';
    public $credit_card_debt_amount = '';
    public $total_outstanding_balance = '';
    public $illness_or_accident_history = '';
    public $known_person_in_company = '';
    public $known_person_relation = '';
    public $available_start_date = '';
    public array $source_channels = [];
    public $source_channel_other = '';
    public $referred_friend_name = '';

    // เอกสารแนบ (เฉพาะไฟล์ที่อัปโหลดใหม่/แทนที่)
    public array $documents = [];

    // ยินยอมและลงชื่อ
    public $data_accuracy_consent = '';
    public $privacy_policy_consent = '';
    public $signature_name = '';

    public function mount(Applicant $applicant): void
    {
        $this->applicant = $applicant->load([
            'position', 'education', 'languages', 'workHistories', 'references', 'documents',
        ]);

        $this->position_id = (string) $applicant->position_id;
        $this->expected_salary = $applicant->expected_salary !== null ? (string) $applicant->expected_salary : '';

        $this->title_th = $applicant->title_th ?? '';
        $this->first_name_th = $applicant->first_name_th ?? '';
        $this->last_name_th = $applicant->last_name_th ?? '';
        $this->nickname_th = $applicant->nickname_th ?? '';
        $this->title_en = $applicant->title_en ?? '';
        $this->first_name_en = $applicant->first_name_en ?? '';
        $this->last_name_en = $applicant->last_name_en ?? '';
        $this->nickname_en = $applicant->nickname_en ?? '';

        $this->date_of_birth = $applicant->date_of_birth?->format('Y-m-d') ?? '';
        $this->nationality = $applicant->nationality ?? '';
        $this->ethnicity = $applicant->ethnicity ?? '';
        $this->religion = $applicant->religion ?? '';
        $this->gender = $applicant->gender ?? '';
        $this->height_cm = $applicant->height_cm !== null ? (string) $applicant->height_cm : '';
        $this->weight_kg = $applicant->weight_kg !== null ? (string) $applicant->weight_kg : '';
        $this->has_congenital_disease = $this->boolToRadio($applicant->has_congenital_disease);
        $this->congenital_disease_detail = $applicant->congenital_disease_detail ?? '';
        $this->has_criminal_record = $this->boolToRadio($applicant->has_criminal_record);
        $this->criminal_record_detail = $applicant->criminal_record_detail ?? '';
        $this->country = $applicant->country ?? '';
        $this->hometown = $applicant->hometown ?? '';

        $this->email = $applicant->email ?? '';
        $this->line_id = $applicant->line_id ?? '';
        $this->phone = $applicant->phone ?? '';
        $this->instagram = $applicant->instagram ?? '';
        $this->twitter = $applicant->twitter ?? '';
        $this->facebook = $applicant->facebook ?? '';

        $this->national_id_number = $applicant->national_id_number ?? '';
        $this->national_id_back_number = $applicant->national_id_back_number ?? '';
        $this->id_issue_date = $applicant->id_issue_date?->format('Y-m-d') ?? '';
        $this->id_expiry_date = $applicant->id_expiry_date?->format('Y-m-d') ?? '';
        $this->current_address = $applicant->current_address ?? '';

        $this->living_status = $applicant->living_status ?? '';
        $this->marital_status = $applicant->marital_status ?? '';
        $this->marriage_registered = $this->boolToRadio($applicant->marriage_registered);
        $this->spouse_name = $applicant->spouse_name ?? '';
        $this->spouse_occupation = $applicant->spouse_occupation ?? '';
        $this->children_count = $applicant->children_count !== null ? (string) $applicant->children_count : '';

        $this->father_name = $applicant->father_name ?? '';
        $this->father_age = $applicant->father_age !== null ? (string) $applicant->father_age : '';
        $this->father_occupation = $applicant->father_occupation ?? '';
        $this->father_alive = $this->boolToRadio($applicant->father_alive);
        $this->mother_name = $applicant->mother_name ?? '';
        $this->mother_age = $applicant->mother_age !== null ? (string) $applicant->mother_age : '';
        $this->mother_occupation = $applicant->mother_occupation ?? '';
        $this->mother_alive = $this->boolToRadio($applicant->mother_alive);
        $this->siblings_count = $applicant->siblings_count !== null ? (string) $applicant->siblings_count : '';
        $this->birth_order = $applicant->birth_order !== null ? (string) $applicant->birth_order : '';

        $this->military_status = $applicant->military_status ?? '';
        $this->has_lese_majeste_record = $this->boolToRadio($applicant->has_lese_majeste_record);
        $this->lese_majeste_detail = $applicant->lese_majeste_detail ?? '';

        foreach (array_keys($this->educationLevels()) as $level) {
            $edu = $applicant->education->firstWhere('degree_level', $level);
            $this->education[$level] = [
                'institution_name' => $edu->institution_name ?? '',
                'province_country' => $edu->province_country ?? '',
                'start_year' => $edu?->start_year !== null ? (string) $edu->start_year : '',
                'end_year' => $edu?->end_year !== null ? (string) $edu->end_year : '',
                'major' => $edu->major ?? '',
                'gpa' => $edu?->gpa !== null ? (string) $edu->gpa : '',
            ];
        }

        foreach (array_keys($this->languageKeys()) as $lang) {
            $row = $applicant->languages->firstWhere('language', $lang);
            $this->languages[$lang] = [
                'speaking_level' => $row?->speaking_level !== null ? (string) $row->speaking_level : '',
                'understanding_level' => $row?->understanding_level !== null ? (string) $row->understanding_level : '',
                'reading_level' => $row?->reading_level !== null ? (string) $row->reading_level : '',
                'writing_level' => $row?->writing_level !== null ? (string) $row->writing_level : '',
            ];
        }
        $this->languages['other']['custom_language_name'] = $applicant->languages->firstWhere('language', 'other')->custom_language_name ?? '';
        $this->language_certificate_note = $applicant->language_certificate_note ?? '';

        for ($i = 1; $i <= 4; $i++) {
            $work = $applicant->workHistories->firstWhere('order_no', $i);
            $this->workHistories[$i] = [
                'company_name' => $work->company_name ?? '',
                'position' => $work->position ?? '',
                'start_year' => $work?->start_year !== null ? (string) $work->start_year : '',
                'end_year' => $work?->end_year !== null ? (string) $work->end_year : '',
                'supervisor_name' => $work->supervisor_name ?? '',
                'supervisor_phone' => $work->supervisor_phone ?? '',
                'last_salary' => $work?->last_salary !== null ? (string) $work->last_salary : '',
                'resignation_reason' => $work->resignation_reason ?? '',
            ];
        }

        for ($i = 1; $i <= 2; $i++) {
            $ref = $applicant->references->firstWhere('order_no', $i);
            $this->references[$i] = [
                'name' => $ref->name ?? '',
                'relationship' => $ref->relationship ?? '',
                'workplace_address' => $ref->workplace_address ?? '',
                'position' => $ref->position ?? '',
                'phone' => $ref->phone ?? '',
            ];
        }

        $this->special_skills = $applicant->special_skills ?? '';
        $this->guarantor_required_ok = $this->boolToRadio($applicant->guarantor_required_ok);
        $this->shift_work_ok = $this->boolToRadio($applicant->shift_work_ok);
        $this->informal_debt_amount = $applicant->informal_debt_amount !== null ? (string) $applicant->informal_debt_amount : '';
        $this->informal_debt_interest_rate = $applicant->informal_debt_interest_rate !== null ? (string) $applicant->informal_debt_interest_rate : '';
        $this->credit_card_debt_amount = $applicant->credit_card_debt_amount !== null ? (string) $applicant->credit_card_debt_amount : '';
        $this->total_outstanding_balance = $applicant->total_outstanding_balance !== null ? (string) $applicant->total_outstanding_balance : '';
        $this->illness_or_accident_history = $applicant->illness_or_accident_history ?? '';
        $this->known_person_in_company = $applicant->known_person_in_company ?? '';
        $this->known_person_relation = $applicant->known_person_relation ?? '';
        $this->available_start_date = $applicant->available_start_date?->format('Y-m-d') ?? '';
        $this->source_channels = $applicant->source_channels ?? [];
        $this->source_channel_other = $applicant->source_channel_other ?? '';
        $this->referred_friend_name = $applicant->referred_friend_name ?? '';

        foreach (array_keys($this->documentTypes()) as $type) {
            $this->documents[$type] = null;
        }

        $this->data_accuracy_consent = $this->boolToRadio($applicant->data_accuracy_consent);
        $this->privacy_policy_consent = $this->boolToRadio($applicant->privacy_policy_consent);
        $this->signature_name = $applicant->signature_name ?? '';
    }

    protected function boolToRadio(?bool $value): string
    {
        return $value === null ? '' : ($value ? '1' : '0');
    }

    protected function deepNullifyEmpty(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => $this->deepNullifyEmpty($v), $value);
        }

        return $value === '' ? null : $value;
    }

    public function documentTypes(): array
    {
        return [
            'photo' => ['th' => 'รูปถ่ายหน้าตรง ขนาด 1 นิ้ว', 'en' => 'One 1-inch photograph', 'required' => true, 'accept' => 'image/*'],
            'education_certificate' => ['th' => 'ใบรับรองการศึกษา', 'en' => 'Educational Certificate', 'required' => true, 'accept' => 'image/*,.pdf'],
            'house_registration' => ['th' => 'สำเนาทะเบียนบ้าน', 'en' => 'Copy of House Registration', 'required' => false, 'accept' => 'image/*,.pdf'],
            'id_card_copy' => ['th' => 'สำเนาบัตรประชาชน', 'en' => 'Copy of National ID Card', 'required' => false, 'accept' => 'image/*,.pdf'],
            'military_certificate' => ['th' => 'ใบผ่านทหาร (ถ้ามี)', 'en' => 'Military Discharge Certificate', 'required' => false, 'accept' => 'image/*,.pdf'],
            'work_certificate_copy' => ['th' => 'สำเนาใบผ่านงาน (ถ้ามี)', 'en' => 'Copy of Employment Certificate', 'required' => false, 'accept' => 'image/*,.pdf'],
            'driving_license' => ['th' => 'สำเนาใบขับขี่ (ถ้ามี)', 'en' => 'Driving License', 'required' => false, 'accept' => 'image/*,.pdf'],
            'other' => ['th' => 'เอกสารอื่นๆ (ถ้ามี)', 'en' => 'Other', 'required' => false, 'accept' => 'image/*,.pdf'],
        ];
    }

    public function sourceChannelOptions(): array
    {
        return [
            'JOBTOPGUN' => 'JOBTOPGUN',
            'JOBTHAI' => 'JOBTHAI',
            'JOBDB' => 'JOBDB',
            'LinkedIn' => 'LinkedIn',
            'Facebook' => 'Facebook',
            'www.skpolymer.com' => 'WWW.SKPOLYMER.COM',
            'Tiktok' => 'Tiktok HRM S.K.Polymer',
            'HR Freelance' => 'HR Freelance',
            'Indeed' => 'Indeed',
            'Headhunter' => 'Headhunter',
            'เพื่อน/ญาติแนะนำ' => 'เพื่อน/ญาติแนะนำ',
            'อื่นๆ' => 'อื่นๆ',
        ];
    }

    public function educationLevels(): array
    {
        return [
            'junior_high' => ['th' => 'มัธยมศึกษาตอนต้น', 'en' => 'Junior High School'],
            'senior_high_vocational_cert' => ['th' => 'มัธยมศึกษาตอนปลาย/ปวช.', 'en' => 'Senior High School / Vocational Certificate'],
            'diploma_high_vocational' => ['th' => 'อนุปริญญา/ปวส.', 'en' => 'Diploma / High Vocational Certificate'],
            'vocational' => ['th' => 'วิชาชีพ', 'en' => 'Vocational'],
            'bachelor' => ['th' => 'ปริญญาตรี', 'en' => 'Bachelor Degree'],
            'master' => ['th' => 'ปริญญาโท', 'en' => 'Master Degree'],
            'other' => ['th' => 'อื่นๆ', 'en' => 'Other'],
        ];
    }

    public function languageKeys(): array
    {
        return [
            'thai' => ['th' => 'ไทย', 'en' => 'Thai'],
            'english' => ['th' => 'อังกฤษ', 'en' => 'English'],
            'chinese' => ['th' => 'จีน', 'en' => 'Chinese'],
            'other' => ['th' => 'อื่นๆ', 'en' => 'Other'],
        ];
    }

    #[Computed]
    public function positions()
    {
        return Position::query()->active()->orderBy('title')->get();
    }

    #[Computed]
    public function age(): ?int
    {
        if (! $this->date_of_birth) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($this->date_of_birth)->age;
        } catch (\Exception) {
            return null;
        }
    }

    public function workDuration(int $orderNo): ?string
    {
        $row = $this->workHistories[$orderNo] ?? null;
        $start = $row['start_year'] ?? null;
        $end = $row['end_year'] ?? null;

        if (! is_numeric($start) || ! is_numeric($end)) {
            return null;
        }

        $years = (int) $end - (int) $start;

        if ($years < 0) {
            return null;
        }

        return $years === 0 ? 'น้อยกว่า 1 ปี' : "{$years} ปี";
    }

    #[Computed]
    public function statusSteps(): array
    {
        $order = ['submitted', 'qualified', 'code_generated', 'awaiting_review', 'passed_test'];
        $labels = ['ส่งใบสมัคร', 'ผ่านเกณฑ์', 'สร้างรหัสแล้ว', 'รอตรวจสอบ', 'ผ่านการทดสอบ'];
        $status = $this->applicant->status;

        if ($status === 'disqualified') {
            $colors = ['green', 'red', 'gray', 'gray', 'gray'];
        } elseif ($status === 'failed_test') {
            $colors = ['green', 'green', 'green', 'green', 'red'];
        } else {
            $index = array_search($status, $order);
            $colors = [];
            foreach ($order as $i => $key) {
                $colors[] = $index === false
                    ? 'gray'
                    : ($i < $index ? 'green' : ($i === $index ? 'yellow' : 'gray'));
            }
        }

        $steps = [];
        foreach ($labels as $i => $label) {
            $steps[] = ['label' => $label, 'color' => $colors[$i]];
        }

        return $steps;
    }

    public function save(): void
    {
        $rules = [
            'position_id' => ['required', 'exists:positions,id'],
            'expected_salary' => ['required', 'numeric', 'min:0'],

            'title_th' => ['required', 'string', 'max:255'],
            'first_name_th' => ['required', 'string', 'max:255'],
            'last_name_th' => ['required', 'string', 'max:255'],
            'nickname_th' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'first_name_en' => ['required', 'string', 'max:255'],
            'last_name_en' => ['required', 'string', 'max:255'],
            'nickname_en' => ['required', 'string', 'max:255'],

            'date_of_birth' => ['required', 'date', 'before:today'],
            'nationality' => ['required', 'string', 'max:255'],
            'ethnicity' => ['required', 'string', 'max:255'],
            'religion' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female,other'],
            'height_cm' => ['required', 'numeric', 'min:0', 'max:300'],
            'weight_kg' => ['required', 'numeric', 'min:0', 'max:500'],
            'has_congenital_disease' => ['required', 'boolean'],
            'congenital_disease_detail' => ['nullable', 'required_if:has_congenital_disease,1', 'string', 'max:255'],
            'has_criminal_record' => ['required', 'boolean'],
            'criminal_record_detail' => ['nullable', 'required_if:has_criminal_record,1', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'hometown' => ['required', 'string', 'max:255'],

            'email' => ['required', 'email', 'max:255'],
            'line_id' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],

            'national_id_number' => ['required', 'string', 'max:20'],
            'national_id_back_number' => ['nullable', 'string', 'max:20'],
            'id_issue_date' => ['nullable', 'date'],
            'id_expiry_date' => ['nullable', 'date'],
            'current_address' => ['required', 'string'],

            'living_status' => ['required', 'in:own_home,rent_home,with_parents,with_other'],
            'marital_status' => ['required', 'in:single,married,divorced,widowed,separated'],
            'marriage_registered' => ['nullable', 'required_if:marital_status,married', 'boolean'],
            'spouse_name' => ['nullable', 'required_if:marital_status,married', 'string', 'max:255'],
            'spouse_occupation' => ['nullable', 'string', 'max:255'],
            'children_count' => ['nullable', 'integer', 'min:0'],

            'father_name' => ['nullable', 'string', 'max:255'],
            'father_age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'father_alive' => ['nullable', 'boolean'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
            'mother_alive' => ['nullable', 'boolean'],
            'siblings_count' => ['nullable', 'integer', 'min:0'],
            'birth_order' => ['nullable', 'integer', 'min:0'],

            'military_status' => ['required', 'in:exempted,completed,awaiting_conscription'],
            'has_lese_majeste_record' => ['required', 'boolean'],
            'lese_majeste_detail' => ['nullable', 'required_if:has_lese_majeste_record,1', 'string', 'max:255'],

            'language_certificate_note' => ['nullable', 'string', 'max:1000'],

            'special_skills' => ['nullable', 'string', 'max:1000'],
            'guarantor_required_ok' => ['nullable', 'boolean'],
            'shift_work_ok' => ['nullable', 'boolean'],
            'informal_debt_amount' => ['nullable', 'numeric', 'min:0'],
            'informal_debt_interest_rate' => ['nullable', 'numeric', 'min:0'],
            'credit_card_debt_amount' => ['nullable', 'numeric', 'min:0'],
            'total_outstanding_balance' => ['nullable', 'numeric', 'min:0'],
            'illness_or_accident_history' => ['nullable', 'string', 'max:1000'],
            'known_person_in_company' => ['nullable', 'string', 'max:255'],
            'known_person_relation' => ['nullable', 'string', 'max:255'],
            'available_start_date' => ['nullable', 'date'],
            'source_channels' => ['nullable', 'array'],
            'source_channel_other' => ['nullable', 'string', 'max:255'],
            'referred_friend_name' => ['nullable', 'string', 'max:255'],

            'signature_name' => ['nullable', 'string', 'max:255'],
            'data_accuracy_consent' => ['nullable', 'boolean'],
            'privacy_policy_consent' => ['nullable', 'boolean'],
        ];

        foreach (array_keys($this->education) as $level) {
            $rules["education.{$level}.institution_name"] = ['nullable', 'string', 'max:255'];
            $rules["education.{$level}.province_country"] = ['nullable', 'string', 'max:255'];
            $rules["education.{$level}.start_year"] = ['nullable', 'integer', 'digits:4'];
            $rules["education.{$level}.end_year"] = ['nullable', 'integer', 'digits:4'];
            $rules["education.{$level}.major"] = ['nullable', 'string', 'max:255'];
            $rules["education.{$level}.gpa"] = ['nullable', 'numeric', 'min:0', 'max:4'];
        }

        foreach (['thai', 'english', 'chinese'] as $lang) {
            foreach (['speaking_level', 'understanding_level', 'reading_level', 'writing_level'] as $skill) {
                // คอลัมน์เหล่านี้ NOT NULL ในฐานข้อมูล (บังคับกรอกเหมือนตอนสมัคร)
                $rules["languages.{$lang}.{$skill}"] = ['required', 'integer', 'between:0,5'];
            }
        }
        $rules['languages.other.custom_language_name'] = ['nullable', 'string', 'max:255'];
        foreach (['speaking_level', 'understanding_level', 'reading_level', 'writing_level'] as $skill) {
            $rules["languages.other.{$skill}"] = ['nullable', 'required_with:languages.other.custom_language_name', 'integer', 'between:0,5'];
        }

        for ($i = 1; $i <= 4; $i++) {
            // company_name และ position เป็น NOT NULL ในฐานข้อมูล จึงต้องคู่กันเสมอ
            $rules["workHistories.{$i}.company_name"] = ['nullable', 'required_with:workHistories.'.$i.'.position', 'string', 'max:255'];
            $rules["workHistories.{$i}.position"] = ['nullable', 'required_with:workHistories.'.$i.'.company_name', 'string', 'max:255'];
            $rules["workHistories.{$i}.start_year"] = ['nullable', 'integer', 'digits:4'];
            $rules["workHistories.{$i}.end_year"] = ['nullable', 'integer', 'digits:4'];
            $rules["workHistories.{$i}.supervisor_name"] = ['nullable', 'string', 'max:255'];
            $rules["workHistories.{$i}.supervisor_phone"] = ['nullable', 'string', 'max:30'];
            $rules["workHistories.{$i}.last_salary"] = ['nullable', 'numeric', 'min:0'];
            $rules["workHistories.{$i}.resignation_reason"] = ['nullable', 'string', 'max:1000'];
        }

        for ($i = 1; $i <= 2; $i++) {
            // name, relationship, phone เป็น NOT NULL ในฐานข้อมูล
            $rules["references.{$i}.name"] = ['nullable', 'string', 'max:255'];
            $rules["references.{$i}.relationship"] = ['nullable', 'required_with:references.'.$i.'.name', 'string', 'max:255'];
            $rules["references.{$i}.workplace_address"] = ['nullable', 'string', 'max:255'];
            $rules["references.{$i}.position"] = ['nullable', 'string', 'max:255'];
            $rules["references.{$i}.phone"] = ['nullable', 'required_with:references.'.$i.'.name', 'string', 'max:30'];
        }

        foreach ($this->documentTypes() as $type => $meta) {
            $rules["documents.{$type}"] = ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }

        $attributes = [
            'position_id' => 'ตำแหน่งที่ต้องการ',
            'expected_salary' => 'เงินเดือนที่ต้องการ',
            'title_th' => 'คำนำหน้า (TH)',
            'first_name_th' => 'ชื่อ (TH)',
            'last_name_th' => 'นามสกุล (TH)',
            'nickname_th' => 'ชื่อเล่น (TH)',
            'title_en' => 'Title (ENG)',
            'first_name_en' => 'Name (ENG)',
            'last_name_en' => 'Surname (ENG)',
            'nickname_en' => 'Nickname (ENG)',
            'date_of_birth' => 'วันเดือนปีเกิด',
            'nationality' => 'สัญชาติ',
            'ethnicity' => 'เชื้อชาติ',
            'religion' => 'ศาสนา',
            'gender' => 'เพศ',
            'height_cm' => 'ส่วนสูง',
            'weight_kg' => 'น้ำหนัก',
            'country' => 'ประเทศ',
            'hometown' => 'ภูมิลำเนา',
            'has_congenital_disease' => 'มีโรคประจำตัว',
            'congenital_disease_detail' => 'รายละเอียดโรคประจำตัว',
            'has_criminal_record' => 'มีประวัติอาชญากรรม',
            'criminal_record_detail' => 'รายละเอียดประวัติอาชญากรรม',
            'email' => 'Email',
            'phone' => 'เบอร์โทรศัพท์',
            'national_id_number' => 'เลขบัตรประชาชน',
            'current_address' => 'ที่อยู่ปัจจุบัน',
            'living_status' => 'สถานะความเป็นอยู่',
            'marital_status' => 'สถานะครอบครัว',
            'marriage_registered' => 'การจดทะเบียนสมรส',
            'spouse_name' => 'ชื่อคู่สมรส',
            'military_status' => 'สถานะภาพทางการทหาร',
            'has_lese_majeste_record' => 'ประวัติหมิ่นพระบรมเดชานุภาพ',
            'lese_majeste_detail' => 'รายละเอียด',
        ];

        $messages = [
            'required' => ':attribute จำเป็นต้องกรอก',
            'required_if' => ':attribute จำเป็นต้องกรอก',
            'required_with' => ':attribute จำเป็นต้องกรอก',
            'email' => ':attribute ไม่ถูกต้อง',
            'date' => ':attribute ไม่ถูกต้อง',
            'before' => ':attribute ต้องเป็นวันที่ก่อนวันนี้',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'integer' => ':attribute ต้องเป็นตัวเลข',
            'digits' => ':attribute ต้องมี 4 หลัก',
            'in' => ':attribute ไม่ถูกต้อง',
            'boolean' => ':attribute ไม่ถูกต้อง',
            'between' => ':attribute ต้องอยู่ระหว่าง 0 ถึง 5',
            'max' => ':attribute ไม่ถูกต้อง',
            'min' => ':attribute ไม่ถูกต้อง',
            'exists' => ':attribute ไม่ถูกต้อง',
            'string' => ':attribute ไม่ถูกต้อง',
            'mimes' => ':attribute ต้องเป็นไฟล์ JPG, PNG หรือ PDF เท่านั้น',
        ];

        $data = $this->validate($rules, $messages, $attributes);
        $data = $this->deepNullifyEmpty($data);

        $scalarFields = [
            'position_id', 'expected_salary',
            'title_th', 'first_name_th', 'last_name_th', 'nickname_th',
            'title_en', 'first_name_en', 'last_name_en', 'nickname_en',
            'date_of_birth', 'nationality', 'ethnicity', 'religion', 'gender',
            'height_cm', 'weight_kg', 'has_congenital_disease', 'congenital_disease_detail',
            'has_criminal_record', 'criminal_record_detail', 'country', 'hometown',
            'email', 'line_id', 'phone', 'instagram', 'twitter', 'facebook',
            'national_id_number', 'national_id_back_number', 'id_issue_date', 'id_expiry_date', 'current_address',
            'living_status', 'marital_status', 'marriage_registered', 'spouse_name', 'spouse_occupation', 'children_count',
            'father_name', 'father_age', 'father_occupation', 'father_alive',
            'mother_name', 'mother_age', 'mother_occupation', 'mother_alive',
            'siblings_count', 'birth_order',
            'military_status', 'has_lese_majeste_record', 'lese_majeste_detail',
            'language_certificate_note',
            'special_skills', 'guarantor_required_ok', 'shift_work_ok',
            'informal_debt_amount', 'informal_debt_interest_rate', 'credit_card_debt_amount', 'total_outstanding_balance',
            'illness_or_accident_history', 'known_person_in_company', 'known_person_relation', 'available_start_date',
            'source_channel_other', 'referred_friend_name',
            'signature_name', 'data_accuracy_consent', 'privacy_policy_consent',
        ];

        $applicantData = collect($data)->only($scalarFields)->all();
        $applicantData['source_channels'] = $this->source_channels ?: null;

        $this->applicant->update($applicantData);

        // ประวัติการศึกษา
        ApplicantEducation::where('applicant_id', $this->applicant->id)->delete();
        foreach ($data['education'] as $level => $fields) {
            $fields = collect($fields)->map(fn ($value) => $value === '' ? null : $value)->all();

            if (blank($fields['institution_name'])) {
                continue;
            }

            ApplicantEducation::create([
                'applicant_id' => $this->applicant->id,
                'degree_level' => $level,
                ...$fields,
            ]);
        }

        // ความสามารถด้านภาษา
        ApplicantLanguage::where('applicant_id', $this->applicant->id)->delete();
        foreach (['thai', 'english', 'chinese'] as $lang) {
            ApplicantLanguage::create([
                'applicant_id' => $this->applicant->id,
                'language' => $lang,
                ...$data['languages'][$lang],
            ]);
        }
        if (filled($data['languages']['other']['custom_language_name'] ?? null)) {
            ApplicantLanguage::create([
                'applicant_id' => $this->applicant->id,
                'language' => 'other',
                ...$data['languages']['other'],
            ]);
        }

        // ประวัติการทำงาน
        ApplicantWorkHistory::where('applicant_id', $this->applicant->id)->delete();
        foreach ($data['workHistories'] as $orderNo => $fields) {
            $fields = collect($fields)->map(fn ($value) => $value === '' ? null : $value)->all();

            if (blank($fields['company_name'])) {
                continue;
            }

            ApplicantWorkHistory::create([
                'applicant_id' => $this->applicant->id,
                'order_no' => $orderNo,
                ...$fields,
            ]);
        }

        // บุคคลอ้างอิง
        ApplicantReference::where('applicant_id', $this->applicant->id)->delete();
        foreach ($data['references'] as $orderNo => $fields) {
            $fields = collect($fields)->map(fn ($value) => $value === '' ? null : $value)->all();

            if (blank($fields['name'])) {
                continue;
            }

            ApplicantReference::create([
                'applicant_id' => $this->applicant->id,
                'order_no' => $orderNo,
                ...$fields,
            ]);
        }

        // เอกสารแนบ (อัปโหลดใหม่/แทนที่เท่านั้น)
        foreach ($this->documents as $type => $file) {
            if (! $file) {
                continue;
            }

            $existing = $this->applicant->documents->firstWhere('document_type', $type);
            if ($existing) {
                Storage::disk('local')->delete($existing->file_path);
            }

            $path = $file->store("applicant-documents/{$this->applicant->id}", 'local');

            ApplicantDocument::updateOrCreate(
                ['applicant_id' => $this->applicant->id, 'document_type' => $type],
                ['file_path' => $path, 'original_filename' => $file->getClientOriginalName()],
            );
        }

        $this->applicant->refresh()->load([
            'position', 'education', 'languages', 'workHistories', 'references', 'documents',
        ]);

        foreach (array_keys($this->documentTypes()) as $type) {
            $this->documents[$type] = null;
        }

        session()->flash('status', 'บันทึกข้อมูลของผู้สมัครเรียบร้อยแล้ว');
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

<div class="flex min-h-screen bg-[#FAF8F3]">
    <x-admin.sidebar />

    <main class="min-w-0 flex-1 p-6 lg:p-10">
        <a href="{{ route('admin.summary.index') }}" wire:navigate class="text-sm font-medium text-stone-500 hover:text-stone-700">
            &larr; กลับไปหน้าสรุปผล
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

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Status stepper --}}
        <div class="mt-6 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
            <h2 class="text-sm font-semibold text-stone-700">สถานะการดำเนินการของผู้สมัคร</h2>
            <div class="mt-6 flex items-start overflow-x-auto pb-1">
                @php $steps = $this->statusSteps(); @endphp
                @foreach ($steps as $i => $step)
                    @if ($i > 0)
                        <div class="mt-5 h-0.5 min-w-4 flex-1 {{ $steps[$i - 1]['color'] === 'green' ? 'bg-green-500' : 'bg-stone-200' }}"></div>
                    @endif

                    @php
                        $circleClass = match ($step['color']) {
                            'green' => 'border-green-500 bg-green-500 text-white',
                            'yellow' => 'border-amber-500 bg-amber-500 text-white',
                            'red' => 'border-red-500 bg-red-500 text-white',
                            default => 'border-stone-300 bg-white text-stone-400',
                        };
                        $textClass = match ($step['color']) {
                            'green' => 'text-green-700',
                            'yellow' => 'text-amber-700',
                            'red' => 'text-red-700',
                            default => 'text-stone-400',
                        };
                    @endphp
                    <div class="flex shrink-0 flex-col items-center px-1 text-center" style="width: 6.5rem">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 text-sm font-semibold {{ $circleClass }}">
                            @if ($step['color'] === 'green')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @elseif ($step['color'] === 'red')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </span>

                        @if ($step['color'] === 'yellow')
                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-1 h-3 w-3 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        @else
                            <span class="mt-1 h-3"></span>
                        @endif

                        <span class="mt-1 text-[11px] font-medium leading-tight sm:text-xs {{ $textClass }}">{{ $step['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <form wire:submit="save" class="mt-6 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-8">
            {{-- ตำแหน่งงานที่สมัคร --}}
            <x-form.section-title thai="ตำแหน่งงานที่สมัคร" english="Position" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.select label="ตำแหน่งที่ต้องการ" model="position_id" required
                    :options="$this->positions->pluck('title', 'id')->all()" />
                <x-form.input label="เงินเดือนที่ต้องการ (บาท)" model="expected_salary" type="number" step="0.01" required placeholder="0" />
            </div>

            {{-- ข้อมูลส่วนตัว --}}
            <x-form.section-title thai="ข้อมูลส่วนตัว" english="Personal information" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-form.select label="คำนำหน้า (TH)" model="title_th" required
                    :options="['นาย' => 'นาย', 'นาง' => 'นาง', 'นางสาว' => 'นางสาว']" />
                <x-form.input label="ชื่อ (TH)" model="first_name_th" required placeholder="ชื่อ" />
                <x-form.input label="นามสกุล (TH)" model="last_name_th" required placeholder="นามสกุล" />
                <x-form.input label="ชื่อเล่น (TH)" model="nickname_th" required placeholder="ชื่อเล่น" />

                <x-form.select label="Title (ENG)" model="title_en" required
                    :options="['Mr.' => 'Mr.', 'Mrs.' => 'Mrs.', 'Ms.' => 'Ms.']" />
                <x-form.input label="Name (ENG)" model="first_name_en" required placeholder="Name" />
                <x-form.input label="Surname (ENG)" model="last_name_en" required placeholder="Surname" />
                <x-form.input label="Nickname (ENG)" model="nickname_en" required placeholder="Nickname" />

                <x-form.input label="วันเดือนปีเกิด" model="date_of_birth" type="date" required live />
                <x-form.input label="อายุ" model="age" value="{{ $this->age }}" disabled placeholder="-" />
                <x-form.input label="สัญชาติ" model="nationality" required placeholder="เช่น ไทย" />
                <x-form.input label="เชื้อชาติ" model="ethnicity" required placeholder="เช่น ไทย" />

                <x-form.input label="ศาสนา" model="religion" required placeholder="เช่น พุทธ" />
                <x-form.select label="เพศ" model="gender" required
                    :options="['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'อื่นๆ']" />
                <x-form.input label="ส่วนสูง (ซม.)" model="height_cm" type="number" step="0.1" required placeholder="ส่วนสูง" />
                <x-form.input label="น้ำหนัก (กก.)" model="weight_kg" type="number" step="0.1" required placeholder="น้ำหนัก" />

                <x-form.radio-group label="มีโรคประจำตัว" model="has_congenital_disease" required live
                    :options="['1' => 'มี', '0' => 'ไม่มี']" />
                @if ($has_congenital_disease === '1')
                    <x-form.input label="รายละเอียดโรคประจำตัว" model="congenital_disease_detail" required wrapper-class="sm:col-span-2" />
                @endif

                <x-form.radio-group label="มีประวัติอาชญากรรม" model="has_criminal_record" required live
                    :options="['1' => 'เคยมี', '0' => 'ไม่มี']" />
                @if ($has_criminal_record === '1')
                    <x-form.input label="รายละเอียดประวัติอาชญากรรม" model="criminal_record_detail" required wrapper-class="sm:col-span-2" />
                @endif

                <x-form.input label="ประเทศ" model="country" required placeholder="เช่น ไทย" />
                <x-form.input label="ภูมิลำเนา" model="hometown" required placeholder="จังหวัด" wrapper-class="sm:col-span-2 lg:col-span-4" />
            </div>

            {{-- ติดต่อ --}}
            <x-form.section-title thai="ช่องทางติดต่อ" english="Contact" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-form.input label="Email" model="email" type="email" required placeholder="you@example.com" />
                <x-form.input label="เบอร์โทรศัพท์" model="phone" required placeholder="08x-xxx-xxxx" />
                <x-form.input label="Line ID" model="line_id" placeholder="Line ID" />
                <x-form.input label="Facebook" model="facebook" placeholder="Facebook" />
                <x-form.input label="Instagram" model="instagram" placeholder="Instagram" />
                <x-form.input label="Twitter" model="twitter" placeholder="Twitter" />
            </div>

            {{-- บัตรประชาชน & ที่อยู่ --}}
            <x-form.section-title thai="บัตรประชาชน & ที่อยู่" english="ID card & Address" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-form.input label="เลขบัตรประชาชน" model="national_id_number" required placeholder="x-xxxx-xxxxx-xx-x" />
                <x-form.input label="เลขหลังบัตรประชาชน" model="national_id_back_number" placeholder="เลขหลังบัตร" />
                <x-form.input label="วันออกบัตร" model="id_issue_date" type="date" />
                <x-form.input label="บัตรหมดอายุ" model="id_expiry_date" type="date" />
            </div>
            <div class="mt-5">
                <x-form.textarea label="ที่อยู่ปัจจุบันที่ติดต่อได้สะดวก" model="current_address" required placeholder="บ้านเลขที่ ถนน ตำบล อำเภอ จังหวัด รหัสไปรษณีย์" />
            </div>

            {{-- สถานะความเป็นอยู่ & ครอบครัว --}}
            <x-form.section-title thai="สถานะความเป็นอยู่ & ครอบครัว" english="Living Status & Family" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.radio-group label="สถานะความเป็นอยู่" model="living_status" required
                    :options="['own_home' => 'บ้านของตัวเอง', 'rent_home' => 'บ้านเช่า', 'with_parents' => 'อยู่กับพ่อแม่', 'with_other' => 'อยู่กับผู้อื่น']" />
                <x-form.radio-group label="สถานะครอบครัว" model="marital_status" required live
                    :options="['single' => 'โสด', 'married' => 'สมรส', 'divorced' => 'หย่าร้าง', 'widowed' => 'หม้าย', 'separated' => 'แยกกันอยู่']" />
            </div>

            @if ($marital_status === 'married')
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-form.radio-group label="การจดทะเบียนสมรส" model="marriage_registered" required
                        :options="['1' => 'จดทะเบียน', '0' => 'ไม่จดทะเบียน']" />
                    <x-form.input label="ชื่อคู่สมรส" model="spouse_name" required placeholder="ชื่อ-นามสกุลคู่สมรส" />
                    <x-form.input label="อาชีพคู่สมรส" model="spouse_occupation" placeholder="อาชีพ" />
                    <x-form.input label="จำนวนบุตร" model="children_count" type="number" placeholder="0" />
                </div>
            @endif

            {{-- บิดามารดาและพี่น้อง --}}
            <x-form.section-title thai="ข้อมูลบิดามารดาและพี่น้อง" english="Parent & Sibling Information" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-form.input label="ชื่อบิดา" model="father_name" placeholder="ชื่อ-นามสกุลบิดา" />
                <x-form.input label="อายุบิดา (ปี)" model="father_age" type="number" placeholder="0" />
                <x-form.input label="อาชีพบิดา" model="father_occupation" placeholder="อาชีพ" />
                <x-form.radio-group label="สถานะบิดา" model="father_alive"
                    :options="['1' => 'มีชีวิตอยู่', '0' => 'เสียชีวิตแล้ว']" />

                <x-form.input label="ชื่อมารดา" model="mother_name" placeholder="ชื่อ-นามสกุลมารดา" />
                <x-form.input label="อายุมารดา (ปี)" model="mother_age" type="number" placeholder="0" />
                <x-form.input label="อาชีพมารดา" model="mother_occupation" placeholder="อาชีพ" />
                <x-form.radio-group label="สถานะมารดา" model="mother_alive"
                    :options="['1' => 'มีชีวิตอยู่', '0' => 'เสียชีวิตแล้ว']" />

                <x-form.input label="จำนวนพี่น้อง (คน)" model="siblings_count" type="number" placeholder="0" />
                <x-form.input label="เป็นบุตรคนที่" model="birth_order" type="number" placeholder="0" />
            </div>

            {{-- ทหาร & ประวัติ --}}
            <x-form.section-title thai="สถานะภาพทางการทหาร & ประวัติ" english="Military Service" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.radio-group label="สถานะภาพทางการทหาร" model="military_status" required
                    :options="['exempted' => 'ได้รับการยกเว้น', 'completed' => 'ผ่านการเกณฑ์ทหารแล้ว', 'awaiting_conscription' => 'รอเข้ารับการเกณฑ์ทหาร']" />
                <x-form.radio-group label="มีประวัติหมิ่นพระบรมเดชานุภาพ" model="has_lese_majeste_record" required live
                    :options="['1' => 'มี', '0' => 'ไม่มี']" />
            </div>
            @if ($has_lese_majeste_record === '1')
                <div class="mt-5">
                    <x-form.input label="รายละเอียด" model="lese_majeste_detail" required />
                </div>
            @endif

            {{-- ประวัติการศึกษา --}}
            @foreach ($this->educationLevels() as $level => $levelLabel)
                <x-form.section-title :thai="$levelLabel['th']" :english="$levelLabel['en']" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-form.input label="ชื่อสถาบัน" model="education.{{ $level }}.institution_name" placeholder="ชื่อสถาบัน" wrapper-class="sm:col-span-2" />
                    <x-form.input label="จังหวัด/ประเทศ" model="education.{{ $level }}.province_country" placeholder="จังหวัด/ประเทศ" />
                    <x-form.input label="เกรดเฉลี่ย (GPA)" model="education.{{ $level }}.gpa" type="number" step="0.01" placeholder="เช่น 3.50" />

                    <x-form.input label="เริ่มต้นปี (พ.ศ.)" model="education.{{ $level }}.start_year" type="number" placeholder="เช่น 2538" />
                    <x-form.input label="ถึงปี (พ.ศ.)" model="education.{{ $level }}.end_year" type="number" placeholder="เช่น 2541" />
                    <x-form.input label="วิชาที่ศึกษา / วุฒิที่ได้รับ" model="education.{{ $level }}.major" placeholder="เช่น สายวิทย์-คณิต" wrapper-class="sm:col-span-2" />
                </div>
            @endforeach

            {{-- ความสามารถด้านภาษา --}}
            @php $levelOptions = ['0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5']; @endphp
            @foreach ($this->languageKeys() as $lang => $langLabel)
                <x-form.section-title :thai="$langLabel['th']" :english="$langLabel['en']" />

                @if ($lang === 'other')
                    <div class="mb-5">
                        <x-form.input label="ระบุชื่อภาษา" model="languages.other.custom_language_name" placeholder="เช่น ญี่ปุ่น, เกาหลี" />
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.radio-group label="การพูด" model="languages.{{ $lang }}.speaking_level" :options="$levelOptions" />
                    <x-form.radio-group label="การเข้าใจ" model="languages.{{ $lang }}.understanding_level" :options="$levelOptions" />
                    <x-form.radio-group label="การอ่าน" model="languages.{{ $lang }}.reading_level" :options="$levelOptions" />
                    <x-form.radio-group label="การเขียน" model="languages.{{ $lang }}.writing_level" :options="$levelOptions" />
                </div>
            @endforeach

            <x-form.section-title thai="ใบรับรองด้านภาษา" english="Certificate of Languages" />
            <x-form.input label="ใบรับรองด้านภาษา (ถ้ามี)" model="language_certificate_note" placeholder="เช่น TOEIC 750, HSK 4" />

            {{-- ประวัติการทำงาน --}}
            @for ($i = 1; $i <= 4; $i++)
                <x-form.section-title thai="สถานประกอบที่ {{ $i }}" english="Establishment {{ $i }}" />

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.input label="ชื่อสถานประกอบการ" model="workHistories.{{ $i }}.company_name" placeholder="ชื่อสถานประกอบการ" />
                    <x-form.input label="ตำแหน่ง" model="workHistories.{{ $i }}.position" placeholder="ตำแหน่ง" />
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <x-form.input label="เริ่มต้นปี (พ.ศ.)" model="workHistories.{{ $i }}.start_year" type="number" placeholder="เช่น 2560" />
                    <x-form.input label="ถึงปี (พ.ศ.)" model="workHistories.{{ $i }}.end_year" type="number" placeholder="เช่น 2563" />
                    <x-form.input label="ระยะเวลาทั้งหมด" model="work_duration_{{ $i }}" value="{{ $this->workDuration($i) }}" disabled placeholder="-" />
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <x-form.input label="ชื่อหัวหน้างาน" model="workHistories.{{ $i }}.supervisor_name" placeholder="ชื่อ-นามสกุล" />
                    <x-form.input label="เบอร์โทร" model="workHistories.{{ $i }}.supervisor_phone" placeholder="เบอร์โทร" />
                    <x-form.input label="เงินเดือนล่าสุด (บาท)" model="workHistories.{{ $i }}.last_salary" type="number" step="0.01" placeholder="เช่น 50000" />
                </div>

                <div class="mt-5">
                    <x-form.textarea label="สาเหตุที่ออก" model="workHistories.{{ $i }}.resignation_reason" placeholder="สาเหตุที่ออกจากงาน" />
                </div>
            @endfor

            {{-- บุคคลอ้างอิง --}}
            @for ($i = 1; $i <= 2; $i++)
                <x-form.section-title
                    thai="บุคคลคนที่ {{ $i }} ที่ไม่ใช่ญาติซึ่งทราบประวัติของท่านและบริษัทสามารถสอบถามได้"
                    english="Persons other than relatives can be contacted" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.input label="ชื่อ - นามสกุล" model="references.{{ $i }}.name" placeholder="ชื่อ - นามสกุล" />
                    <x-form.input label="ความสัมพันธ์" model="references.{{ $i }}.relationship" placeholder="เช่น เพื่อน" />
                </div>
                <div class="mt-5">
                    <x-form.input label="สถานที่ทำงาน-ที่อยู่" model="references.{{ $i }}.workplace_address" placeholder="สถานที่ทำงาน / ที่อยู่" />
                </div>
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.input label="ตำแหน่ง" model="references.{{ $i }}.position" placeholder="ตำแหน่ง" />
                    <x-form.input label="เบอร์โทร" model="references.{{ $i }}.phone" placeholder="เบอร์โทร" />
                </div>
            @endfor

            {{-- ความสามารถพิเศษ & เงื่อนไขการทำงาน --}}
            <x-form.section-title thai="ความสามารถพิเศษ & เงื่อนไขการทำงาน" english="Special ability & Working conditions" />
            <div class="mb-5">
                <x-form.textarea label="ความรู้ความสามารถพิเศษ" model="special_skills" placeholder="ความรู้ความสามารถพิเศษ" />
            </div>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-form.radio-group label="สามารถหาผู้ค้ำประกันได้หรือไม่" model="guarantor_required_ok" :options="['1' => 'ได้', '0' => 'ไม่ได้']" />
                <x-form.radio-group label="สามารถทำงานเป็นกะหมุนเวียนได้หรือไม่" model="shift_work_ok" :options="['1' => 'ได้', '0' => 'ไม่ได้']" />
            </div>

            {{-- ข้อมูลด้านการเงิน --}}
            <x-form.section-title thai="ข้อมูลด้านการเงิน" english="Financial Information" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-form.input label="เงินที่ชำระหนี้นอกระบบ (บาท/เดือน)" model="informal_debt_amount" type="number" step="0.01" placeholder="0" />
                <x-form.input label="ดอกเบี้ย (ร้อยละ/เดือน)" model="informal_debt_interest_rate" type="number" step="0.01" placeholder="0" />
                <x-form.input label="วงเงินบัตรเครดิต/บัตรกดเงินสด (บาท)" model="credit_card_debt_amount" type="number" step="0.01" placeholder="0" />
                <x-form.input label="ยอดค้างชำระ (บาท)" model="total_outstanding_balance" type="number" step="0.01" placeholder="0" />
            </div>

            {{-- ประวัติสุขภาพ --}}
            <x-form.section-title thai="ประวัติสุขภาพ" english="Health History" />
            <x-form.textarea label="ในช่วง 3 ปีที่ผ่านมา เคยเจ็บป่วยเข้าโรงพยาบาลหรือประสบอุบัติเหตุหรือไม่ (ถ้ามีระบุรายละเอียด)" model="illness_or_accident_history" placeholder="รายละเอียด (ถ้ามี)" />

            {{-- ข้อมูลอื่นๆ --}}
            <x-form.section-title thai="ข้อมูลอื่นๆ" english="Additional information" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-form.input label="บุคคลในบริษัทที่รู้จัก/แนะนำ" model="known_person_in_company" placeholder="ชื่อ-นามสกุล" />
                <x-form.input label="ความสัมพันธ์" model="known_person_relation" placeholder="ความสัมพันธ์" />
                <x-form.input label="วันที่พร้อมเริ่มงาน" model="available_start_date" type="date" />
            </div>
            <div class="mt-5">
                <x-form.checkbox-group label="ช่องทางที่ทราบข่าวการรับสมัคร" model="source_channels" :options="$this->sourceChannelOptions()" live />
            </div>
            @if (in_array('อื่นๆ', $source_channels) || in_array('เพื่อน/ญาติแนะนำ', $source_channels))
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @if (in_array('อื่นๆ', $source_channels))
                        <x-form.input label="ระบุช่องทางอื่นๆ" model="source_channel_other" placeholder="ระบุ" />
                    @endif
                    @if (in_array('เพื่อน/ญาติแนะนำ', $source_channels))
                        <x-form.input label="ชื่อเพื่อน/ญาติที่แนะนำ" model="referred_friend_name" placeholder="ชื่อ-นามสกุล" />
                    @endif
                </div>
            @endif

            {{-- การยินยอมและลงชื่อ --}}
            <x-form.section-title thai="การยินยอมและลงชื่อ" english="Consent & Signature" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-form.radio-group label="การรับรองความถูกต้องของข้อมูล" model="data_accuracy_consent" :options="['1' => 'ยินยอม', '0' => 'ไม่ยินยอม']" />
                <x-form.radio-group label="การยินยอมตามนโยบายความเป็นส่วนตัว" model="privacy_policy_consent" :options="['1' => 'ยินยอม', '0' => 'ไม่ยินยอม']" />
                <x-form.input label="ลงชื่อผู้สมัครงาน (พิมพ์ชื่อ-นามสกุล)" model="signature_name" placeholder="ชื่อ-นามสกุล" />
            </div>

            {{-- เอกสารแนบ --}}
            <x-form.section-title thai="เอกสารแนบ" english="Attachments" />
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                @php $documentsByType = $applicant->documents->keyBy('document_type'); @endphp
                @foreach ($this->documentTypes() as $type => $meta)
                    @php $existingDoc = $documentsByType->get($type); @endphp
                    <div class="rounded-xl border border-stone-200 p-4">
                        <p class="text-sm font-medium text-stone-700">
                            {{ $meta['th'] }} <span class="font-normal text-stone-400">/ {{ $meta['en'] }}</span>
                            @if ($meta['required'])<span class="text-red-500">*</span>@endif
                        </p>

                        @if ($existingDoc)
                            <div class="mt-2 flex items-center justify-between gap-2 rounded-lg bg-green-50 px-3 py-2 text-xs text-green-700">
                                <span class="truncate">{{ $existingDoc->original_filename }}</span>
                                <a href="{{ route('secure-files.applicant-document', $existingDoc) }}" target="_blank"
                                   class="shrink-0 font-semibold underline hover:text-green-900">
                                    ดูตัวอย่าง/ดาวน์โหลด
                                </a>
                            </div>
                        @else
                            <p class="mt-2 text-xs text-stone-400">ยังไม่มีไฟล์แนบ</p>
                        @endif

                        <div class="mt-3">
                            <x-form.file
                                :label="$existingDoc ? 'แนบไฟล์ใหม่ (แทนที่ไฟล์เดิม)' : 'อัปโหลดไฟล์'"
                                model="documents.{{ $type }}"
                                :accept="$meta['accept']"
                            />
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 flex justify-end border-t border-stone-100 pt-6">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="save">บันทึกการแก้ไข</span>
                    <span wire:loading wire:target="save">กำลังบันทึก...</span>
                </button>
            </div>
        </form>
    </main>
</div>
