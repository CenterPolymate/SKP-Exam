@php
    // $locale: null = admin view (เก็บพฤติกรรมเดิมไว้ทุกจุด, หลายช่องเป็นไทยอย่างเดียวอยู่แล้ว)
    // 'th'/'en' = หน้าตรวจสอบข้อมูลของผู้สมัคร (แยกภาษาเดียวตามที่เลือกไว้)
    $locale = $locale ?? null;
    $t = fn ($th, $en) => $locale === null ? $th : ($locale === 'en' ? $en : $th);

    $genderLabels = [
        'male' => $locale === null ? 'ชาย / Male' : $t('ชาย', 'Male'),
        'female' => $locale === null ? 'หญิง / Female' : $t('หญิง', 'Female'),
        'other' => $locale === null ? 'อื่นๆ / Other' : $t('อื่นๆ', 'Other'),
    ];
    $livingStatusLabels = [
        'own_home' => $t('บ้านส่วนตัว', 'Own home'), 'rent_home' => $t('บ้านเช่า', 'Rented home'),
        'with_parents' => $t('อาศัยบิดามารดา', 'Living with parents'), 'with_other' => $t('อาศัยอยู่กับผู้อื่น', 'Living with others'),
    ];
    $maritalStatusLabels = [
        'single' => $t('โสด', 'Single'), 'married' => $t('แต่งงาน', 'Married'), 'divorced' => $t('หย่า', 'Divorced'),
        'widowed' => $t('หม้าย', 'Widowed'), 'separated' => $t('แยกกันอยู่', 'Separated'),
    ];
    $militaryStatusLabels = [
        'exempted' => $t('ได้รับการยกเว้น', 'Exempted'), 'completed' => $t('ผ่านการเกณฑ์ทหารแล้ว', 'Completed'),
        'awaiting_conscription' => $t('รอเข้ารับการเกณฑ์ทหาร', 'Awaiting conscription'),
    ];
    $degreeLevelLabels = [
        'junior_high' => $t('มัธยมศึกษาตอนต้น', 'Junior High School'), 'senior_high_vocational_cert' => $t('มัธยมศึกษาตอนปลาย/ปวช.', 'Senior High School / Vocational Certificate'),
        'diploma_high_vocational' => $t('อนุปริญญา/ปวส.', 'Diploma / High Vocational Certificate'), 'vocational' => $t('อาชีวศึกษา', 'Vocational'),
        'bachelor' => $t('ปริญญาตรี', 'Bachelor Degree'), 'master' => $t('ปริญญาโท', 'Master Degree'), 'other' => $t('อื่นๆ', 'Other'),
    ];
    $languageLabels = ['thai' => $t('ไทย', 'Thai'), 'english' => $t('อังกฤษ', 'English'), 'chinese' => $t('จีน', 'Chinese'), 'other' => $t('อื่นๆ', 'Other')];
    $languageTier = fn ($v) => $locale === null
        ? ($v >= 4 ? 'ดีมาก (Excellent)' : ($v >= 2 ? 'ดี (Good)' : 'พอใช้ (Fair)'))
        : ($v >= 4 ? $t('ดีมาก', 'Excellent') : ($v >= 2 ? $t('ดี', 'Good') : $t('พอใช้', 'Fair')));
    $documentTypeLabels = [
        'photo' => $t('รูปถ่ายหน้าตรง 1 นิ้ว', 'One 1-inch photograph'), 'education_certificate' => $t('ใบรับรองการศึกษา', 'Educational Certificate'),
        'house_registration' => $t('สำเนาทะเบียนบ้าน', 'Copy of House Registration'), 'id_card_copy' => $t('สำเนาบัตรประชาชน', 'Copy of National ID Card'),
        'military_certificate' => $t('ใบผ่านทหาร', 'Military Discharge Certificate'), 'work_certificate_copy' => $t('สำเนาใบผ่านงาน', 'Copy of Employment Certificate'),
        'driving_license' => $t('สำเนาใบขับขี่', 'Driving License'), 'other' => $t('เอกสารอื่นๆ', 'Other'),
    ];
    $documentsByType = $a->documents->keyBy('document_type');
    $yesNo = fn ($v) => is_null($v) ? '-' : ($v ? $t('ใช่', 'Yes') : $t('ไม่ใช่', 'No'));
    $fmtDate = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : '-';
@endphp

{{-- ประวัติส่วนตัว --}}
<x-form.section-title thai="ประวัติส่วนตัว" english="Personal Background" :locale="$locale" />
<dl class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
    <x-review.field :label="$t('ชื่อ-นามสกุล (TH)', 'Full Name (TH)')">{{ $a->title_th }} {{ $a->first_name_th }} {{ $a->last_name_th }}</x-review.field>
    <x-review.field :label="$t('ชื่อ-นามสกุล (ENG)', 'Full Name (ENG)')">{{ $a->title_en }} {{ $a->first_name_en }} {{ $a->last_name_en }}</x-review.field>
    <x-review.field :label="$t('ชื่อเล่น', 'Nickname')">{{ $a->nickname_th }} / {{ $a->nickname_en }}</x-review.field>
    <x-review.field :label="$t('เพศ', 'Gender')">{{ $genderLabels[$a->gender] ?? '-' }}</x-review.field>
    <x-review.field :label="$t('วันเกิด / อายุ', 'Date of Birth / Age')">{{ $fmtDate($a->date_of_birth) }} ({{ $a->date_of_birth ? \Illuminate\Support\Carbon::parse($a->date_of_birth)->age : '-' }} {{ $t('ปี', 'yrs') }})</x-review.field>
    <x-review.field :label="$t('สัญชาติ / เชื้อชาติ', 'Nationality / Race')">{{ $a->nationality }} / {{ $a->ethnicity }}</x-review.field>
    <x-review.field :label="$t('ศาสนา', 'Religion')">{{ $a->religion }}</x-review.field>
    <x-review.field :label="$t('ส่วนสูง / น้ำหนัก', 'Height / Weight')">{{ $a->height_cm }} {{ $t('ซม.', 'cm') }} / {{ $a->weight_kg }} {{ $t('กก.', 'kg') }}</x-review.field>
    <x-review.field :label="$t('ตำแหน่งที่สมัคร', 'Position Applied For')">{{ $a->position->title ?? '-' }}</x-review.field>
    <x-review.field :label="$t('เงินเดือนที่ต้องการ', 'Expected Salary')">{{ number_format((float) $a->expected_salary, 2) }} {{ $t('บาท', 'THB') }}</x-review.field>
    <x-review.field :label="$t('เบอร์โทร / Email', 'Phone / Email')">{{ $a->phone }} / {{ $a->email }}</x-review.field>
    <x-review.field label="Line ID">{{ $a->line_id }}</x-review.field>
    <x-review.field :label="$t('เลขบัตรประชาชน', 'National ID Number')">{{ $a->national_id_number }}</x-review.field>
    <x-review.field :label="$t('ที่อยู่ปัจจุบัน', 'Current Address')">{{ $a->current_address }}</x-review.field>
    <x-review.field :label="$t('สถานะความเป็นอยู่', 'Living Status')">{{ $livingStatusLabels[$a->living_status] ?? '-' }}</x-review.field>
    <x-review.field :label="$t('สถานะครอบครัว', 'Marital Status')">{{ $maritalStatusLabels[$a->marital_status] ?? '-' }}</x-review.field>
    <x-review.field :label="$t('สถานะภาพทางการทหาร', 'Military Status')">{{ $militaryStatusLabels[$a->military_status] ?? '-' }}</x-review.field>
    <x-review.field :label="$t('ประวัติหมิ่นพระมหากษัตริย์', 'Anti Monarch Record')">{{ $yesNo($a->has_lese_majeste_record) }}</x-review.field>
</dl>

{{-- ประวัติการศึกษา --}}
<x-form.section-title thai="ประวัติการศึกษา" english="Educational Background" :locale="$locale" />
@forelse ($a->education as $edu)
    <div class="mb-3 rounded-lg border border-stone-200 p-3 text-sm">
        <span class="font-semibold text-amber-700">{{ $degreeLevelLabels[$edu->degree_level] ?? $edu->degree_level }}</span>
        — {{ $edu->institution_name }} ({{ $edu->province_country ?: '-' }}) {{ $edu->start_year }}-{{ $edu->end_year }}
        {{ $edu->major ? '· '.$edu->major : '' }} {{ $edu->gpa ? '· GPA '.$edu->gpa : '' }}
    </div>
@empty
    <p class="text-sm text-stone-400">{{ $t('ไม่ได้ระบุข้อมูลการศึกษา', 'No education information provided') }}</p>
@endforelse

{{-- ความสามารถด้านภาษา --}}
<x-form.section-title thai="ความสามารถด้านภาษา" english="Languages" :locale="$locale" />
@forelse ($a->languages as $lang)
    <div class="mb-2 text-sm">
        <span class="font-semibold text-stone-700">{{ $lang->language === 'other' ? $lang->custom_language_name : ($languageLabels[$lang->language] ?? '-') }}</span>
        — {{ $t('พูด', 'Speaking') }}: {{ $languageTier($lang->speaking_level) }}, {{ $t('เข้าใจ', 'Understanding') }}: {{ $languageTier($lang->understanding_level) }},
        {{ $t('อ่าน', 'Reading') }}: {{ $languageTier($lang->reading_level) }}, {{ $t('เขียน', 'Writing') }}: {{ $languageTier($lang->writing_level) }}
    </div>
@empty
    <p class="text-sm text-stone-400">{{ $t('ไม่ได้ระบุความสามารถด้านภาษา', 'No language skills provided') }}</p>
@endforelse

{{-- ประวัติการทำงาน --}}
<x-form.section-title thai="ประวัติการทำงาน" english="Employment History" :locale="$locale" />
@forelse ($a->workHistories as $work)
    <div class="mb-3 rounded-lg border border-stone-200 p-3 text-sm">
        <span class="font-semibold text-amber-700">{{ $work->company_name }}</span> — {{ $work->position }}
        ({{ $work->start_year }}-{{ $work->end_year }}) {{ $t('หัวหน้างาน', 'Supervisor') }}: {{ $work->supervisor_name ?: '-' }}
        {{ $t('เงินเดือนล่าสุด', 'Last Salary') }}: {{ $work->last_salary ? number_format($work->last_salary, 2) : '-' }} {{ $t('บาท', 'THB') }}
    </div>
@empty
    <p class="text-sm text-stone-400">{{ $t('ไม่ได้ระบุประวัติการทำงาน', 'No employment history provided') }}</p>
@endforelse

{{-- ข้อมูลอื่นๆ --}}
<x-form.section-title thai="ข้อมูลอื่นๆ" english="Other Information" :locale="$locale" />
@foreach ($a->references as $ref)
    <div class="mb-2 text-sm">
        <span class="font-semibold text-stone-700">{{ $t('บุคคลอ้างอิงคนที่', 'Reference Person No.') }} {{ $ref->order_no }}:</span>
        {{ $ref->name }} ({{ $ref->relationship }}) {{ $t('โทร', 'Tel') }} {{ $ref->phone }}
    </div>
@endforeach
<dl class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
    <x-review.field :label="$t('ความรู้ความสามารถพิเศษ', 'Special Skills')">{{ $a->special_skills }}</x-review.field>
    <x-review.field :label="$t('สามารถหาผู้ค้ำประกันได้', 'Can Provide Guarantor')">{{ $yesNo($a->guarantor_required_ok) }}</x-review.field>
    <x-review.field :label="$t('ทำงานเป็นกะหมุนเวียนได้', 'Can Work Rotating Shifts')">{{ $yesNo($a->shift_work_ok) }}</x-review.field>
    <x-review.field :label="$t('วันที่พร้อมเริ่มงาน', 'Available Start Date')">{{ $fmtDate($a->available_start_date) }}</x-review.field>
    <x-review.field :label="$t('ช่องทางที่ทราบข่าว', 'How You Heard About This Job')">{{ implode(', ', (array) $a->source_channels) }}</x-review.field>
</dl>

{{-- เอกสารแนบ --}}
<x-form.section-title thai="เอกสารแนบ" english="Attachments" :locale="$locale" />
<div class="flex flex-wrap gap-2">
    @foreach ($documentTypeLabels as $type => $label)
        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs {{ $documentsByType->has($type) ? 'bg-green-50 text-green-700' : 'bg-stone-100 text-stone-400' }}">
            {{ $label }} {{ $documentsByType->has($type) ? '✓' : '—' }}
        </span>
    @endforeach
</div>

{{-- การยินยอมและลงชื่อ --}}
<x-form.section-title thai="การยินยอมและลงชื่อ" english="Consent & Signature" :locale="$locale" />
<dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <x-review.field :label="$t('การรับรองความถูกต้องของข้อมูล', 'Data Accuracy Certification')">{{ $yesNo($a->data_accuracy_consent) }}</x-review.field>
    <x-review.field :label="$t('การยินยอมตามนโยบายความเป็นส่วนตัว', 'Privacy Policy Consent')">{{ $yesNo($a->privacy_policy_consent) }}</x-review.field>
    <x-review.field :label="$t('ลงชื่อผู้สมัครงาน', 'Applicant Signature')">{{ $a->signature_name }}</x-review.field>
</dl>
