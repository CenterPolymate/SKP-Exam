@php
    $genderLabels = ['male' => 'ชาย / Male', 'female' => 'หญิง / Female', 'other' => 'อื่นๆ / Other'];
    $livingStatusLabels = [
        'own_home' => 'บ้านส่วนตัว', 'rent_home' => 'บ้านเช่า',
        'with_parents' => 'อาศัยบิดามารดา', 'with_other' => 'อาศัยอยู่กับผู้อื่น',
    ];
    $maritalStatusLabels = [
        'single' => 'โสด', 'married' => 'แต่งงาน', 'divorced' => 'หย่า',
        'widowed' => 'หม้าย', 'separated' => 'แยกกันอยู่',
    ];
    $militaryStatusLabels = [
        'exempted' => 'ได้รับการยกเว้น', 'completed' => 'ผ่านการเกณฑ์ทหารแล้ว',
        'awaiting_conscription' => 'รอเข้ารับการเกณฑ์ทหาร',
    ];
    $degreeLevelLabels = [
        'junior_high' => 'มัธยมศึกษาตอนต้น', 'senior_high_vocational_cert' => 'มัธยมศึกษาตอนปลาย/ปวช.',
        'diploma_high_vocational' => 'อนุปริญญา/ปวส.', 'vocational' => 'อาชีวศึกษา',
        'bachelor' => 'ปริญญาตรี', 'master' => 'ปริญญาโท', 'other' => 'อื่นๆ',
    ];
    $languageLabels = ['thai' => 'ไทย', 'english' => 'อังกฤษ', 'chinese' => 'จีน', 'other' => 'อื่นๆ'];
    $languageTier = fn ($v) => $v >= 4 ? 'ดีมาก (Excellent)' : ($v >= 2 ? 'ดี (Good)' : 'พอใช้ (Fair)');
    $documentTypeLabels = [
        'photo' => 'รูปถ่ายหน้าตรง 1 นิ้ว', 'education_certificate' => 'ใบรับรองการศึกษา',
        'house_registration' => 'สำเนาทะเบียนบ้าน', 'id_card_copy' => 'สำเนาบัตรประชาชน',
        'military_certificate' => 'ใบผ่านทหาร', 'work_certificate_copy' => 'สำเนาใบผ่านงาน',
        'driving_license' => 'สำเนาใบขับขี่', 'other' => 'เอกสารอื่นๆ',
    ];
    $documentsByType = $a->documents->keyBy('document_type');
    $yesNo = fn ($v) => is_null($v) ? '-' : ($v ? 'ใช่' : 'ไม่ใช่');
    $fmtDate = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : '-';
@endphp

{{-- ประวัติส่วนตัว --}}
<x-form.section-title thai="ประวัติส่วนตัว" english="Personal Background" />
<dl class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
    <x-review.field label="ชื่อ-นามสกุล (TH)">{{ $a->title_th }} {{ $a->first_name_th }} {{ $a->last_name_th }}</x-review.field>
    <x-review.field label="Name (ENG)">{{ $a->title_en }} {{ $a->first_name_en }} {{ $a->last_name_en }}</x-review.field>
    <x-review.field label="ชื่อเล่น">{{ $a->nickname_th }} / {{ $a->nickname_en }}</x-review.field>
    <x-review.field label="เพศ">{{ $genderLabels[$a->gender] ?? '-' }}</x-review.field>
    <x-review.field label="วันเกิด / อายุ">{{ $fmtDate($a->date_of_birth) }} ({{ $a->date_of_birth ? \Illuminate\Support\Carbon::parse($a->date_of_birth)->age : '-' }} ปี)</x-review.field>
    <x-review.field label="สัญชาติ / เชื้อชาติ">{{ $a->nationality }} / {{ $a->ethnicity }}</x-review.field>
    <x-review.field label="ศาสนา">{{ $a->religion }}</x-review.field>
    <x-review.field label="ส่วนสูง / น้ำหนัก">{{ $a->height_cm }} ซม. / {{ $a->weight_kg }} กก.</x-review.field>
    <x-review.field label="ตำแหน่งที่สมัคร">{{ $a->position->title ?? '-' }}</x-review.field>
    <x-review.field label="เงินเดือนที่ต้องการ">{{ number_format((float) $a->expected_salary, 2) }} บาท</x-review.field>
    <x-review.field label="เบอร์โทร / Email">{{ $a->phone }} / {{ $a->email }}</x-review.field>
    <x-review.field label="Line ID">{{ $a->line_id }}</x-review.field>
    <x-review.field label="เลขบัตรประชาชน">{{ $a->national_id_number }}</x-review.field>
    <x-review.field label="ที่อยู่ปัจจุบัน">{{ $a->current_address }}</x-review.field>
    <x-review.field label="สถานะความเป็นอยู่">{{ $livingStatusLabels[$a->living_status] ?? '-' }}</x-review.field>
    <x-review.field label="สถานะครอบครัว">{{ $maritalStatusLabels[$a->marital_status] ?? '-' }}</x-review.field>
    <x-review.field label="สถานะภาพทางการทหาร">{{ $militaryStatusLabels[$a->military_status] ?? '-' }}</x-review.field>
    <x-review.field label="ประวัติหมิ่นพระบรมเดชานุภาพ">{{ $yesNo($a->has_lese_majeste_record) }}</x-review.field>
</dl>

{{-- ประวัติการศึกษา --}}
<x-form.section-title thai="ประวัติการศึกษา" english="Educational Background" />
@forelse ($a->education as $edu)
    <div class="mb-3 rounded-lg border border-stone-200 p-3 text-sm">
        <span class="font-semibold text-amber-700">{{ $degreeLevelLabels[$edu->degree_level] ?? $edu->degree_level }}</span>
        — {{ $edu->institution_name }} ({{ $edu->province_country ?: '-' }}) {{ $edu->start_year }}-{{ $edu->end_year }}
        {{ $edu->major ? '· '.$edu->major : '' }} {{ $edu->gpa ? '· GPA '.$edu->gpa : '' }}
    </div>
@empty
    <p class="text-sm text-stone-400">ไม่ได้ระบุข้อมูลการศึกษา</p>
@endforelse

{{-- ความสามารถด้านภาษา --}}
<x-form.section-title thai="ความสามารถด้านภาษา" english="Languages" />
@forelse ($a->languages as $lang)
    <div class="mb-2 text-sm">
        <span class="font-semibold text-stone-700">{{ $lang->language === 'other' ? $lang->custom_language_name : ($languageLabels[$lang->language] ?? '-') }}</span>
        — พูด: {{ $languageTier($lang->speaking_level) }}, เข้าใจ: {{ $languageTier($lang->understanding_level) }},
        อ่าน: {{ $languageTier($lang->reading_level) }}, เขียน: {{ $languageTier($lang->writing_level) }}
    </div>
@empty
    <p class="text-sm text-stone-400">ไม่ได้ระบุความสามารถด้านภาษา</p>
@endforelse

{{-- ประวัติการทำงาน --}}
<x-form.section-title thai="ประวัติการทำงาน" english="Employment History" />
@forelse ($a->workHistories as $work)
    <div class="mb-3 rounded-lg border border-stone-200 p-3 text-sm">
        <span class="font-semibold text-amber-700">{{ $work->company_name }}</span> — {{ $work->position }}
        ({{ $work->start_year }}-{{ $work->end_year }}) หัวหน้างาน: {{ $work->supervisor_name ?: '-' }}
        เงินเดือนล่าสุด: {{ $work->last_salary ? number_format($work->last_salary, 2) : '-' }} บาท
    </div>
@empty
    <p class="text-sm text-stone-400">ไม่ได้ระบุประวัติการทำงาน</p>
@endforelse

{{-- ข้อมูลอื่นๆ --}}
<x-form.section-title thai="ข้อมูลอื่นๆ" english="Other Information" />
@foreach ($a->references as $ref)
    <div class="mb-2 text-sm">
        <span class="font-semibold text-stone-700">บุคคลอ้างอิงคนที่ {{ $ref->order_no }}:</span>
        {{ $ref->name }} ({{ $ref->relationship }}) โทร {{ $ref->phone }}
    </div>
@endforeach
<dl class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
    <x-review.field label="ความรู้ความสามารถพิเศษ">{{ $a->special_skills }}</x-review.field>
    <x-review.field label="สามารถหาผู้ค้ำประกันได้">{{ $yesNo($a->guarantor_required_ok) }}</x-review.field>
    <x-review.field label="ทำงานเป็นกะหมุนเวียนได้">{{ $yesNo($a->shift_work_ok) }}</x-review.field>
    <x-review.field label="วันที่พร้อมเริ่มงาน">{{ $fmtDate($a->available_start_date) }}</x-review.field>
    <x-review.field label="ช่องทางที่ทราบข่าว">{{ implode(', ', (array) $a->source_channels) }}</x-review.field>
</dl>

{{-- เอกสารแนบ --}}
<x-form.section-title thai="เอกสารแนบ" english="Attachments" />
<div class="flex flex-wrap gap-2">
    @foreach ($documentTypeLabels as $type => $label)
        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs {{ $documentsByType->has($type) ? 'bg-green-50 text-green-700' : 'bg-stone-100 text-stone-400' }}">
            {{ $label }} {{ $documentsByType->has($type) ? '✓' : '—' }}
        </span>
    @endforeach
</div>

{{-- การยินยอมและลงชื่อ --}}
<x-form.section-title thai="การยินยอมและลงชื่อ" english="Consent & Signature" />
<dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <x-review.field label="การรับรองความถูกต้องของข้อมูล">{{ $yesNo($a->data_accuracy_consent) }}</x-review.field>
    <x-review.field label="การยินยอมตามนโยบายความเป็นส่วนตัว">{{ $yesNo($a->privacy_policy_consent) }}</x-review.field>
    <x-review.field label="ลงชื่อผู้สมัครงาน">{{ $a->signature_name }}</x-review.field>
</dl>
