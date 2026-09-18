<?php

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantEducation;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantReference;
use App\Models\ApplicantWorkHistory;
use App\Models\Position;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.application')] class extends Component
{
    use WithFileUploads;

    // ลำดับขั้นตอนของ wizard ("done" = จบเท่าที่พัฒนาไว้ตอนนี้)
    public string $step = 'personal';
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
    public $country = 'ไทย';
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

    public ?int $applicantId = null;

    // ประวัติการศึกษา (5.3) - เก็บเป็น array แยกตามระดับการศึกษา
    public array $education = [];

    // ความสามารถด้านภาษา (5.4)
    public array $languages = [];
    public $language_certificate_note = '';

    // ประวัติการทำงาน (5.5) - สูงสุด 4 สถานประกอบการ
    public array $workHistories = [];

    // ข้อมูลอื่นๆ & เอกสาร (5.6)
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
    public array $documents = [];

    // วัน-เวลาทำงาน & ยินยอม (5.7)
    public $data_accuracy_consent = '';
    public $privacy_policy_consent = '';
    public $signature_name = '';

    public function mount(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            $this->references[$i] = [
                'name' => '',
                'relationship' => '',
                'workplace_address' => '',
                'position' => '',
                'phone' => '',
            ];
        }

        foreach (array_keys($this->documentTypes()) as $type) {
            $this->documents[$type] = null;
        }

        foreach (array_keys($this->educationLevels()) as $level) {
            $this->education[$level] = [
                'institution_name' => '',
                'province_country' => '',
                'start_year' => '',
                'end_year' => '',
                'major' => '',
                'gpa' => '',
            ];
        }

        foreach (array_keys($this->languageKeys()) as $lang) {
            $this->languages[$lang] = [
                'speaking_level' => '',
                'understanding_level' => '',
                'reading_level' => '',
                'writing_level' => '',
            ];
        }
        $this->languages['other']['custom_language_name'] = '';

        for ($i = 1; $i <= 4; $i++) {
            $this->workHistories[$i] = [
                'company_name' => '',
                'position' => '',
                'start_year' => '',
                'end_year' => '',
                'supervisor_name' => '',
                'supervisor_phone' => '',
                'last_salary' => '',
                'resignation_reason' => '',
            ];
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
            return Carbon::parse($this->date_of_birth)->age;
        } catch (\Exception) {
            return null;
        }
    }

    protected function rules(): array
    {
        return [
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
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => ':attribute จำเป็นต้องกรอก',
            'required_if' => ':attribute จำเป็นต้องกรอก',
            'email' => ':attribute ไม่ถูกต้อง',
            'date' => ':attribute ไม่ถูกต้อง',
            'before' => ':attribute ต้องเป็นวันที่ก่อนวันนี้',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
            'in' => ':attribute ไม่ถูกต้อง',
            'boolean' => ':attribute ไม่ถูกต้อง',
            'max' => ':attribute ยาวเกินไป',
            'min' => ':attribute ไม่ถูกต้อง',
            'exists' => ':attribute ไม่ถูกต้อง',
            'string' => ':attribute ไม่ถูกต้อง',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
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
    }

    public function submitPersonal(): void
    {
        $data = $this->validate();

        // ฟิลด์ที่เว้นว่างไว้ (nullable) ต้องบันทึกเป็น NULL ไม่ใช่ empty string
        // มิฉะนั้น PostgreSQL จะปฏิเสธค่าว่างสำหรับคอลัมน์ date/integer/boolean
        $data = collect($data)->map(fn ($value) => $value === '' ? null : $value)->all();

        if ($this->applicantId) {
            $applicant = Applicant::findOrFail($this->applicantId);
            $applicant->update($data);
        } else {
            $applicant = Applicant::create($data);
            $this->applicantId = $applicant->id;
        }

        $this->step = 'education';
    }

    public function submitEducation(): void
    {
        $rules = [];
        foreach (array_keys($this->education) as $level) {
            $rules["education.{$level}.institution_name"] = ['nullable', 'string', 'max:255'];
            $rules["education.{$level}.province_country"] = ['nullable', 'string', 'max:255'];
            $rules["education.{$level}.start_year"] = ['nullable', 'integer', 'digits:4'];
            $rules["education.{$level}.end_year"] = ['nullable', 'integer', 'digits:4'];
            $rules["education.{$level}.major"] = ['nullable', 'string', 'max:255'];
            $rules["education.{$level}.gpa"] = ['nullable', 'numeric', 'min:0', 'max:4'];
        }

        $data = $this->validate($rules, [
            'integer' => ':attribute ต้องเป็นตัวเลข',
            'digits' => ':attribute ต้องมี 4 หลัก',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'max' => ':attribute ไม่ถูกต้อง',
        ]);

        // ลบข้อมูลการศึกษาเดิมของผู้สมัครคนนี้ก่อน แล้วค่อยบันทึกใหม่ทั้งหมด (กันข้อมูลซ้ำเวลากลับมาแก้ไข)
        ApplicantEducation::where('applicant_id', $this->applicantId)->delete();

        foreach ($data['education'] as $level => $fields) {
            $fields = collect($fields)->map(fn ($value) => $value === '' ? null : $value)->all();

            // ข้ามระดับการศึกษาที่ไม่ได้กรอกชื่อสถาบัน
            if (blank($fields['institution_name'])) {
                continue;
            }

            ApplicantEducation::create([
                'applicant_id' => $this->applicantId,
                'degree_level' => $level,
                ...$fields,
            ]);
        }

        $this->step = 'language';
    }

    public function submitLanguage(): void
    {
        $rules = [
            'language_certificate_note' => ['nullable', 'string', 'max:1000'],
        ];

        foreach (['thai', 'english', 'chinese'] as $lang) {
            foreach (['speaking_level', 'understanding_level', 'reading_level', 'writing_level'] as $skill) {
                $rules["languages.{$lang}.{$skill}"] = ['required', 'integer', 'between:0,5'];
            }
        }

        $rules['languages.other.custom_language_name'] = ['nullable', 'string', 'max:255'];
        foreach (['speaking_level', 'understanding_level', 'reading_level', 'writing_level'] as $skill) {
            $rules["languages.other.{$skill}"] = ['nullable', 'required_with:languages.other.custom_language_name', 'integer', 'between:0,5'];
        }

        $skillLabels = [
            'speaking_level' => 'การพูด',
            'understanding_level' => 'การเข้าใจ',
            'reading_level' => 'การอ่าน',
            'writing_level' => 'การเขียน',
        ];
        $attributes = ['languages.other.custom_language_name' => 'ชื่อภาษาอื่นๆ'];
        foreach ($this->languageKeys() as $lang => $labels) {
            foreach ($skillLabels as $skill => $skillLabel) {
                $attributes["languages.{$lang}.{$skill}"] = "{$labels['th']} - {$skillLabel}";
            }
        }

        $data = $this->validate($rules, [
            'required' => ':attribute จำเป็นต้องกรอก',
            'required_with' => ':attribute จำเป็นต้องกรอกเมื่อระบุชื่อภาษา',
            'integer' => ':attribute ต้องเป็นตัวเลข',
            'between' => ':attribute ต้องอยู่ระหว่าง 0 ถึง 5',
        ], $attributes);

        // ลบข้อมูลภาษาเดิมของผู้สมัครคนนี้ก่อน แล้วค่อยบันทึกใหม่ทั้งหมด
        ApplicantLanguage::where('applicant_id', $this->applicantId)->delete();

        foreach (['thai', 'english', 'chinese'] as $lang) {
            ApplicantLanguage::create([
                'applicant_id' => $this->applicantId,
                'language' => $lang,
                ...$data['languages'][$lang],
            ]);
        }

        if (filled($data['languages']['other']['custom_language_name'] ?? null)) {
            ApplicantLanguage::create([
                'applicant_id' => $this->applicantId,
                'language' => 'other',
                ...$data['languages']['other'],
            ]);
        }

        Applicant::whereKey($this->applicantId)->update([
            'language_certificate_note' => $data['language_certificate_note'] ?: null,
        ]);

        $this->step = 'work';
    }

    public function submitWork(): void
    {
        $rules = [];
        $attributes = [];
        for ($i = 1; $i <= 4; $i++) {
            $rules["workHistories.{$i}.company_name"] = ['nullable', 'required_with:workHistories.'.$i.'.position', 'string', 'max:255'];
            $rules["workHistories.{$i}.position"] = ['nullable', 'required_with:workHistories.'.$i.'.company_name', 'string', 'max:255'];
            $rules["workHistories.{$i}.start_year"] = ['nullable', 'integer', 'digits:4'];
            $rules["workHistories.{$i}.end_year"] = ['nullable', 'integer', 'digits:4'];
            $rules["workHistories.{$i}.supervisor_name"] = ['nullable', 'string', 'max:255'];
            $rules["workHistories.{$i}.supervisor_phone"] = ['nullable', 'string', 'max:30'];
            $rules["workHistories.{$i}.last_salary"] = ['nullable', 'numeric', 'min:0'];
            $rules["workHistories.{$i}.resignation_reason"] = ['nullable', 'string', 'max:1000'];

            $attributes["workHistories.{$i}.company_name"] = "ชื่อสถานประกอบการที่ {$i}";
            $attributes["workHistories.{$i}.position"] = "ตำแหน่งที่ {$i}";
        }

        $data = $this->validate($rules, [
            'required_with' => ':attribute จำเป็นต้องกรอก',
            'integer' => ':attribute ต้องเป็นตัวเลข',
            'digits' => ':attribute ต้องมี 4 หลัก',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
        ], $attributes);

        // ลบข้อมูลประวัติการทำงานเดิมของผู้สมัครคนนี้ก่อน แล้วค่อยบันทึกใหม่ทั้งหมด
        ApplicantWorkHistory::where('applicant_id', $this->applicantId)->delete();

        foreach ($data['workHistories'] as $orderNo => $fields) {
            $fields = collect($fields)->map(fn ($value) => $value === '' ? null : $value)->all();

            // ข้ามสถานประกอบการที่ไม่ได้กรอกชื่อ
            if (blank($fields['company_name'])) {
                continue;
            }

            ApplicantWorkHistory::create([
                'applicant_id' => $this->applicantId,
                'order_no' => $orderNo,
                ...$fields,
            ]);
        }

        $this->step = 'other';
    }

    public function submitOther(): void
    {
        $rules = [
            'references.1.name' => ['required', 'string', 'max:255'],
            'references.1.relationship' => ['required', 'string', 'max:255'],
            'references.1.workplace_address' => ['required', 'string', 'max:255'],
            'references.1.position' => ['nullable', 'string', 'max:255'],
            'references.1.phone' => ['required', 'string', 'max:30'],

            'references.2.name' => ['nullable', 'string', 'max:255'],
            'references.2.relationship' => ['nullable', 'required_with:references.2.name', 'string', 'max:255'],
            'references.2.workplace_address' => ['nullable', 'string', 'max:255'],
            'references.2.position' => ['nullable', 'string', 'max:255'],
            'references.2.phone' => ['nullable', 'required_with:references.2.name', 'string', 'max:30'],

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
        ];

        $attributes = [
            'references.1.name' => 'ชื่อ-นามสกุล บุคคลที่ 1',
            'references.1.relationship' => 'ความสัมพันธ์ บุคคลที่ 1',
            'references.1.workplace_address' => 'สถานที่ทำงาน-ที่อยู่ บุคคลที่ 1',
            'references.1.phone' => 'เบอร์โทร บุคคลที่ 1',
            'references.2.relationship' => 'ความสัมพันธ์ บุคคลที่ 2',
            'references.2.phone' => 'เบอร์โทร บุคคลที่ 2',
        ];

        foreach ($this->documentTypes() as $type => $meta) {
            $rules["documents.{$type}"] = [
                $meta['required'] ? 'required' : 'nullable',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120',
            ];
            $attributes["documents.{$type}"] = $meta['th'];
        }

        $data = $this->validate($rules, [
            'required' => ':attribute จำเป็นต้องกรอก',
            'required_with' => ':attribute จำเป็นต้องกรอก',
            'numeric' => ':attribute ต้องเป็นตัวเลข',
            'date' => ':attribute ไม่ถูกต้อง',
            'mimes' => ':attribute ต้องเป็นไฟล์ JPG, PNG หรือ PDF เท่านั้น',
            'max' => ':attribute มีขนาดใหญ่เกินไป (สูงสุด 5MB)',
        ], $attributes);

        // แปลง empty string ของฟิลด์ระดับบนสุดให้เป็น NULL (ไม่กระทบ references/documents ที่เป็น array)
        $data = collect($data)->map(fn ($value) => $value === '' ? null : $value)->all();

        // อัปเดตข้อมูลในตาราง applicants
        Applicant::whereKey($this->applicantId)->update([
            'special_skills' => $data['special_skills'],
            'guarantor_required_ok' => $data['guarantor_required_ok'],
            'shift_work_ok' => $data['shift_work_ok'],
            'informal_debt_amount' => $data['informal_debt_amount'],
            'informal_debt_interest_rate' => $data['informal_debt_interest_rate'],
            'credit_card_debt_amount' => $data['credit_card_debt_amount'],
            'total_outstanding_balance' => $data['total_outstanding_balance'],
            'illness_or_accident_history' => $data['illness_or_accident_history'],
            'known_person_in_company' => $data['known_person_in_company'],
            'known_person_relation' => $data['known_person_relation'],
            'available_start_date' => $data['available_start_date'],
            'source_channels' => $this->source_channels ?: null,
            'source_channel_other' => $data['source_channel_other'],
            'referred_friend_name' => $data['referred_friend_name'],
        ]);

        // บุคคลอ้างอิง
        ApplicantReference::where('applicant_id', $this->applicantId)->delete();
        foreach ($data['references'] as $orderNo => $fields) {
            $fields = collect($fields)->map(fn ($value) => $value === '' ? null : $value)->all();

            if (blank($fields['name'])) {
                continue;
            }

            ApplicantReference::create([
                'applicant_id' => $this->applicantId,
                'order_no' => $orderNo,
                ...$fields,
            ]);
        }

        // เอกสารแนบ
        foreach ($this->documents as $type => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store("applicant-documents/{$this->applicantId}", 'public');

            ApplicantDocument::updateOrCreate(
                ['applicant_id' => $this->applicantId, 'document_type' => $type],
                ['file_path' => $path, 'original_filename' => $file->getClientOriginalName()],
            );
        }

        $this->step = 'schedule';
    }

    public function submitSchedule(): void
    {
        $data = $this->validate([
            'data_accuracy_consent' => ['accepted'],
            'privacy_policy_consent' => ['accepted'],
            'signature_name' => ['required', 'string', 'max:255'],
        ], [
            'accepted' => ':attribute ต้องกดยอมรับก่อนจึงจะส่งใบสมัครได้',
            'required' => ':attribute จำเป็นต้องกรอก',
        ], [
            'data_accuracy_consent' => 'การรับรองความถูกต้องของข้อมูล',
            'privacy_policy_consent' => 'การยินยอมตามนโยบายคุ้มครองข้อมูลส่วนบุคคล',
            'signature_name' => 'ลงชื่อผู้สมัครงาน',
        ]);

        Applicant::whereKey($this->applicantId)->update([
            'data_accuracy_consent' => true,
            'privacy_policy_consent' => true,
            'signature_name' => $data['signature_name'],
            'signed_at' => now()->toDateString(),
        ]);

        $this->step = 'review';
    }

    #[Computed]
    public function reviewApplicant()
    {
        return Applicant::with([
            'position', 'education', 'languages', 'workHistories', 'references', 'documents',
        ])->find($this->applicantId);
    }

    public function confirmSubmit(): void
    {
        Applicant::whereKey($this->applicantId)->update([
            'status' => 'submitted',
        ]);

        $this->step = 'done';
    }

    public function downloadPdf()
    {
        $applicant = Applicant::with([
            'position', 'education', 'languages', 'workHistories', 'references', 'documents',
        ])->findOrFail($this->applicantId);

        $pdf = app('dompdf.wrapper')->loadView('pdf.application', ['applicant' => $applicant]);

        $filename = 'APP-'.str_pad($applicant->id, 6, '0', STR_PAD_LEFT).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }
}; ?>

<div class="mx-auto w-full max-w-4xl px-4 py-10 sm:px-6">

        @if ($step === 'done')
            {{-- Success state --}}
            <div class="rounded-2xl bg-white p-10 text-center shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                    <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                </div>
                <h1 class="mt-4 text-xl font-semibold text-stone-800">ส่งใบสมัครงานเรียบร้อยแล้ว</h1>
                <p class="mt-2 text-sm text-stone-500">
                    เลขที่ใบสมัครอ้างอิงของคุณคือ <span class="font-semibold text-amber-700">APP-{{ str_pad($applicantId, 6, '0', STR_PAD_LEFT) }}</span>
                </p>
                <p class="mt-1 text-sm text-stone-500">
                    ขอบคุณที่สนใจร่วมงานกับ Grand SK ทีมงาน HR จะตรวจสอบใบสมัครของท่านและติดต่อกลับผ่านช่องทางที่ท่านให้ไว้
                </p>

                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <button
                        type="button"
                        wire:click="downloadPdf"
                        wire:loading.attr="disabled"
                        wire:target="downloadPdf"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <svg wire:loading.remove wire:target="downloadPdf" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v7.19l2.72-2.72a.75.75 0 1 1 1.06 1.06l-4 4a.75.75 0 0 1-1.06 0l-4-4a.75.75 0 1 1 1.06-1.06l2.72 2.72V3.75A.75.75 0 0 1 10 3ZM4 15a1 1 0 1 0 0 2h12a1 1 0 1 0 0-2H4Z" clip-rule="evenodd" /></svg>
                        <svg wire:loading wire:target="downloadPdf" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path></svg>
                        <span wire:loading.remove wire:target="downloadPdf">ดาวน์โหลดใบสมัคร (PDF)</span>
                        <span wire:loading wire:target="downloadPdf">กำลังสร้างไฟล์...</span>
                    </button>
                    <a href="{{ url('/') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-stone-200 px-5 py-2.5 text-sm font-medium text-stone-700 transition hover:border-amber-500 hover:bg-amber-50 hover:text-amber-700">
                        กลับหน้าแรก
                    </a>
                </div>
            </div>
        @else
            @php
                $stepKeys = ['personal', 'education', 'language', 'work', 'other', 'schedule', 'review'];
                $stepLabels = [
                    'ประวัติส่วนตัว', 'ประวัติการศึกษา', 'ความสามารถด้านภาษา',
                    'ประวัติการทำงาน', 'ข้อมูลอื่นๆ & เอกสาร', 'วัน-เวลาทำงาน', 'ตรวจสอบข้อมูล',
                ];
                $currentStepIndex = array_search($step, $stepKeys);
            @endphp

            {{-- Step progress --}}
            <div class="overflow-x-auto">
                <div class="flex min-w-max sm:min-w-0">
                    @foreach ($stepLabels as $index => $stepLabel)
                        <div class="flex-1 border-b-2 px-2 pb-2.5 text-center text-[11px] leading-tight sm:text-xs
                            {{ $index === $currentStepIndex
                                ? 'border-amber-500 font-semibold text-amber-700'
                                : ($index < $currentStepIndex ? 'border-amber-300 text-amber-600' : 'border-stone-200 text-stone-400') }}">
                            {{ $stepLabel }}
                        </div>
                    @endforeach
                </div>
            </div>

        @endif

        @if ($step === 'personal')
            <form wire:submit="submitPersonal" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">ประวัติส่วนตัว <span class="font-normal text-stone-400">/ Personal Background</span></h1>
                <p class="mt-1 text-sm text-stone-500">กรอกข้อมูลให้ครบถ้วนและตรงตามความเป็นจริง</p>

                {{-- ตำแหน่งงานที่สมัคร --}}
                <x-form.section-title thai="ตำแหน่งงานที่สมัคร" english="Position" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.select label="ตำแหน่งที่ต้องการ" model="position_id" required placeholder="--เลือกตำแหน่งที่สนใจ--"
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

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <a href="{{ url('/') }}" wire:navigate class="text-sm font-medium text-stone-500 hover:text-stone-700">
                        &larr; กลับหน้าแรก
                    </a>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitPersonal">บันทึกและไปขั้นตอนถัดไป</span>
                        <span wire:loading wire:target="submitPersonal">กำลังบันทึก...</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'education')
            <form wire:submit="submitEducation" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">ประวัติการศึกษา <span class="font-normal text-stone-400">/ Educational Background</span></h1>
                <p class="mt-1 text-sm text-stone-500">กรอกข้อมูลการศึกษาตามระดับที่ท่านสำเร็จ (เว้นว่างได้หากไม่มี)</p>

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

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'personal')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; ย้อนกลับ
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitEducation">บันทึกและไปขั้นตอนถัดไป</span>
                        <span wire:loading wire:target="submitEducation">กำลังบันทึก...</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'language')
            @php
                $levelOptions = ['0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'];
            @endphp
            <form wire:submit="submitLanguage" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">ความสามารถด้านภาษา <span class="font-normal text-stone-400">/ Languages</span></h1>
                <p class="mt-1 text-sm text-stone-500">ประเมินระดับความสามารถทางภาษาตามความเป็นจริง โดยเรียงลำดับจากน้อย (0 = ไม่มีความสามารถเลย) ไปถึงมากที่สุด (5 = คล่องแคล่ว)</p>

                @foreach ($this->languageKeys() as $lang => $langLabel)
                    <x-form.section-title :thai="$langLabel['th']" :english="$langLabel['en']" />

                    @if ($lang === 'other')
                        <div class="mb-5">
                            <x-form.input label="ระบุชื่อภาษา" model="languages.other.custom_language_name" placeholder="เช่น ญี่ปุ่น, เกาหลี" />
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.radio-group label="การพูด" model="languages.{{ $lang }}.speaking_level" :required="$lang !== 'other'" :options="$levelOptions" />
                        <x-form.radio-group label="การเข้าใจ" model="languages.{{ $lang }}.understanding_level" :required="$lang !== 'other'" :options="$levelOptions" />
                        <x-form.radio-group label="การอ่าน" model="languages.{{ $lang }}.reading_level" :required="$lang !== 'other'" :options="$levelOptions" />
                        <x-form.radio-group label="การเขียน" model="languages.{{ $lang }}.writing_level" :required="$lang !== 'other'" :options="$levelOptions" />
                    </div>
                @endforeach

                <x-form.section-title thai="ใบรับรองด้านภาษา" english="Certificate of Languages" />
                <x-form.input label="ใบรับรองด้านภาษา (ถ้ามี)" model="language_certificate_note" placeholder="เช่น TOEIC 750, HSK 4" />

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'education')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; ย้อนกลับ
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitLanguage">บันทึกและไปขั้นตอนถัดไป</span>
                        <span wire:loading wire:target="submitLanguage">กำลังบันทึก...</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'work')
            <form wire:submit="submitWork" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">ประวัติการทำงาน <span class="font-normal text-stone-400">/ Employment History</span></h1>
                <p class="mt-1 text-sm text-stone-500">กรอกประวัติการทำงานย้อนหลัง เรียงจากล่าสุด (เว้นว่างได้หากไม่มี) สูงสุด 4 สถานประกอบการ</p>
                <p class="mt-1 text-xs text-stone-400">** หมายเหตุ ** ประสบการณ์ฝึกงานสามารถใส่ได้เช่นกัน</p>

                @for ($i = 1; $i <= 4; $i++)
                    <x-form.section-title thai="สถานประกอบที่ {{ $i }}" english="Establishment {{ $i }}" />

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.input label="ชื่อสถานประกอบการ" model="workHistories.{{ $i }}.company_name" placeholder="ชื่อสถานประกอบการ" />
                        <x-form.input label="ตำแหน่ง" model="workHistories.{{ $i }}.position" placeholder="ตำแหน่ง" />
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <x-form.input label="เริ่มต้นปี (พ.ศ.)" model="workHistories.{{ $i }}.start_year" type="number" placeholder="เช่น 2560" live />
                        <x-form.input label="ถึงปี (พ.ศ.)" model="workHistories.{{ $i }}.end_year" type="number" placeholder="เช่น 2563" live />
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

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'language')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; ย้อนกลับ
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitWork">บันทึกและไปขั้นตอนถัดไป</span>
                        <span wire:loading wire:target="submitWork">กำลังบันทึก...</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'other')
            <form wire:submit="submitOther" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">ข้อมูลอื่นๆ <span class="font-normal text-stone-400">/ Other information</span></h1>
                <p class="mt-1 text-sm text-stone-500">ข้อมูลเพิ่มเติมสำหรับการพิจารณา</p>

                @for ($i = 1; $i <= 2; $i++)
                    <x-form.section-title
                        thai="บุคคลคนที่ {{ $i }} ที่ไม่ใช่ญาติซึ่งทราบประวัติของท่านและบริษัทสามารถสอบถามได้"
                        english="Persons other than relatives can be contacted" />
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.input label="ชื่อ - นามสกุล" model="references.{{ $i }}.name" :required="$i === 1" placeholder="ชื่อ - นามสกุล" />
                        <x-form.input label="ความสัมพันธ์" model="references.{{ $i }}.relationship" :required="$i === 1" placeholder="เช่น เพื่อน" />
                    </div>
                    <div class="mt-5">
                        <x-form.input label="สถานที่ทำงาน-ที่อยู่" model="references.{{ $i }}.workplace_address" :required="$i === 1" placeholder="สถานที่ทำงาน / ที่อยู่" />
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.input label="ตำแหน่ง" model="references.{{ $i }}.position" placeholder="ตำแหน่ง" />
                        <x-form.input label="เบอร์โทร" model="references.{{ $i }}.phone" :required="$i === 1" placeholder="เบอร์โทร" />
                    </div>
                @endfor

                <x-form.section-title thai="ความสามารถพิเศษ & เงื่อนไขการทำงาน" english="Special ability & Working conditions" />
                <div class="mb-5">
                    <x-form.textarea label="ความรู้ความสามารถพิเศษ" model="special_skills" placeholder="ความรู้ความสามารถพิเศษ" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.radio-group label="สามารถหาผู้ค้ำประกันได้หรือไม่" model="guarantor_required_ok" :options="['1' => 'ได้', '0' => 'ไม่ได้']" />
                    <x-form.radio-group label="สามารถทำงานเป็นกะหมุนเวียนได้หรือไม่" model="shift_work_ok" :options="['1' => 'ได้', '0' => 'ไม่ได้']" />
                </div>

                <x-form.section-title thai="ข้อมูลด้านการเงิน" english="Financial Information" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-form.input label="เงินที่ชำระหนี้นอกระบบ (บาท/เดือน)" model="informal_debt_amount" type="number" step="0.01" placeholder="0" />
                    <x-form.input label="ดอกเบี้ย (ร้อยละ/เดือน)" model="informal_debt_interest_rate" type="number" step="0.01" placeholder="0" />
                    <x-form.input label="วงเงินบัตรเครดิต/บัตรกดเงินสด (บาท)" model="credit_card_debt_amount" type="number" step="0.01" placeholder="0" />
                    <x-form.input label="ยอดค้างชำระ (บาท)" model="total_outstanding_balance" type="number" step="0.01" placeholder="0" />
                </div>

                <x-form.section-title thai="ประวัติสุขภาพ" english="Health History" />
                <x-form.textarea label="ในช่วง 3 ปีที่ผ่านมา เคยเจ็บป่วยเข้าโรงพยาบาลหรือประสบอุบัติเหตุหรือไม่ (ถ้ามีระบุรายละเอียด)" model="illness_or_accident_history" placeholder="รายละเอียด (ถ้ามี)" />

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

                <x-form.section-title thai="เอกสารแนบ" english="Attachment (JPG, PNG, PDF)" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach ($this->documentTypes() as $type => $meta)
                        <x-form.file
                            :label="$meta['th']"
                            :hint="$meta['en']"
                            model="documents.{{ $type }}"
                            :accept="$meta['accept']"
                            :required="$meta['required']"
                        />
                    @endforeach
                </div>

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'work')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; ย้อนกลับ
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitOther">บันทึกและไปขั้นตอนถัดไป</span>
                        <span wire:loading wire:target="submitOther">กำลังบันทึก...</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'schedule')
            <form wire:submit="submitSchedule" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">วันและเวลาปฏิบัติงาน <span class="font-normal text-stone-400">/ Working days and hours</span></h1>
                <p class="mt-1 text-sm text-stone-500">กรุณาอ่านรายละเอียดเวลาปฏิบัติงานและยินยอมข้อตกลงก่อนส่งใบสมัคร</p>

                <div class="mt-6 space-y-4">
                    <div class="rounded-xl bg-amber-50/60 p-5">
                        <h3 class="text-sm font-semibold text-amber-700">กลุ่มพนักงานที่ไม่เกี่ยวข้องกับการผลิต <span class="font-normal text-stone-400">/ Employee group that is not related to the production</span></h3>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-stone-600">
                            <li>ในสัปดาห์ที่ 1, 3 ปฏิบัติงานวันจันทร์ ถึง วันศุกร์ และวันเสาร์ทำงานในรูปแบบ Work From Anywhere เวลาปฏิบัติงานครึ่งวันเช้า</li>
                            <li>ในสัปดาห์ที่ 2, 4 ปฏิบัติงานวันจันทร์ ถึง วันศุกร์ วันเสาร์เป็นวันหยุดประจำสัปดาห์</li>
                            <li>ในสัปดาห์ที่ 5 ปฏิบัติงานวันเสาร์ (ทำกิจกรรม CSR) เวลาปฏิบัติงานตามข้อบังคับการทำงานของแต่ละบริษัท</li>
                        </ul>
                    </div>

                    <div class="rounded-xl bg-amber-50/60 p-5">
                        <h3 class="text-sm font-semibold text-amber-700">กลุ่มพนักงานที่เกี่ยวข้องกับการผลิต <span class="font-normal text-stone-400">/ Employee group that is related to the production</span></h3>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-stone-600">
                            <li>ปฏิบัติงานวันจันทร์ ถึง วันเสาร์ เวลาปฏิบัติงานตามข้อบังคับการทำงานของแต่ละบริษัท (เลือกหยุดวันเสาร์ได้ 2 วันต่อเดือน)</li>
                        </ul>
                    </div>
                </div>

                <x-form.section-title thai="ข้อตกลงและการยินยอม" english="Consent" />
                <div class="space-y-4">
                    <x-form.checkbox model="data_accuracy_consent" required>
                        ข้าพเจ้าขอรับรองว่าข้อความข้างต้นทั้งหมดนี้เป็นความจริงทุกประการ หากข้อความตอนหนึ่งตอนใดไม่ตรงกับความเป็นจริง ข้าพเจ้าขอยอมรับว่าการว่าจ้างที่ตกลงนั้นเป็นอันโมฆะทันที
                        และยินยอมให้เก็บ ใช้ เปิดเผย ตรวจสอบ ข้อมูลดังกล่าวได้ตลอดเวลาที่จำเป็นเพื่อการสมัครงาน และการเป็นพนักงานของบริษัทได้
                        <span class="mt-1 block text-xs text-stone-400">I certify that my answers are true and complete to the best of my knowledge. If this application leads to employment, I understand that false or misleading information in my application or interview may result in my release. I consent to the disclosure and verification on personal information all the time for applying for a job and being an employee of the company.</span>
                    </x-form.checkbox>

                    <x-form.checkbox model="privacy_policy_consent" required>
                        ข้าพเจ้าได้อ่านนโยบายการคุ้มครองข้อมูลส่วนบุคคล สำหรับบุคลากรของบริษัท ข้าพเจ้ารับทราบและยินยอมให้เก็บ ใช้ เปิดเผย ตรวจสอบ
                        ข้อมูลดังกล่าวได้ตลอดเวลาที่จำเป็นเพื่อการสมัครงาน และตลอดระยะเวลาการเป็นพนักงานของบริษัท
                        <span class="mt-1 block text-xs text-stone-400">I have read the privacy policy for company personnel. I acknowledge and consent to disclosure and verification on personal information all the time for applying for a job and being an employee of the company.</span>
                    </x-form.checkbox>
                </div>

                <div class="mt-6 max-w-sm">
                    <x-form.input label="ลงชื่อผู้สมัครงาน (พิมพ์ชื่อ-นามสกุล)" model="signature_name" required placeholder="ชื่อ-นามสกุล" />
                </div>

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'other')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; ย้อนกลับ
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitSchedule">ไปที่หน้าตรวจสอบข้อมูล</span>
                        <span wire:loading wire:target="submitSchedule">กำลังบันทึก...</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'review')
            @php
                $a = $this->reviewApplicant;
            @endphp

            <div class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">ตรวจสอบข้อมูล <span class="font-normal text-stone-400">/ Review before submitting</span></h1>
                <p class="mt-1 text-sm text-stone-500">กรุณาตรวจสอบข้อมูลทั้งหมดให้ถูกต้องก่อนกดยืนยันส่งใบสมัคร ท่านสามารถดาวน์โหลดตัวอย่าง PDF เพื่อตรวจสอบได้เช่นกัน</p>

                <div class="mt-4">
                    <button
                        type="button"
                        wire:click="downloadPdf"
                        wire:loading.attr="disabled"
                        wire:target="downloadPdf"
                        class="inline-flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 disabled:opacity-60"
                    >
                        <svg wire:loading.remove wire:target="downloadPdf" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v7.19l2.72-2.72a.75.75 0 1 1 1.06 1.06l-4 4a.75.75 0 0 1-1.06 0l-4-4a.75.75 0 1 1 1.06-1.06l2.72 2.72V3.75A.75.75 0 0 1 10 3ZM4 15a1 1 0 1 0 0 2h12a1 1 0 1 0 0-2H4Z" clip-rule="evenodd" /></svg>
                        <svg wire:loading wire:target="downloadPdf" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path></svg>
                        <span wire:loading.remove wire:target="downloadPdf">ดาวน์โหลดตัวอย่าง PDF</span>
                        <span wire:loading wire:target="downloadPdf">กำลังสร้างไฟล์...</span>
                    </button>
                </div>

                @include('partials.applicant-review', ['a' => $a])

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'schedule')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; ย้อนกลับไปแก้ไข
                    </button>
                    <button
                        type="button"
                        wire:click="confirmSubmit"
                        wire:loading.attr="disabled"
                        wire:target="confirmSubmit"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="confirmSubmit">ยืนยันและส่งใบสมัคร</span>
                        <span wire:loading wire:target="confirmSubmit">กำลังส่งใบสมัคร...</span>
                    </button>
                </div>
            </div>
        @endif
    </div>
