<?php

use App\Models\Applicant;
use App\Models\Position;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.application')] class extends Component
{
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

    public bool $submitted = false;
    public ?int $applicantId = null;

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

    public function submit(): void
    {
        $data = $this->validate();

        // ฟิลด์ที่เว้นว่างไว้ (nullable) ต้องบันทึกเป็น NULL ไม่ใช่ empty string
        // มิฉะนั้น PostgreSQL จะปฏิเสธค่าว่างสำหรับคอลัมน์ date/integer/boolean
        $data = collect($data)->map(fn ($value) => $value === '' ? null : $value)->all();

        $applicant = Applicant::create($data);

        $this->applicantId = $applicant->id;
        $this->submitted = true;
    }
}; ?>

<div class="mx-auto w-full max-w-4xl px-4 py-10 sm:px-6">

        @if ($submitted)
            {{-- Success state --}}
            <div class="rounded-2xl bg-white p-10 text-center shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                    <svg class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                </div>
                <h1 class="mt-4 text-xl font-semibold text-stone-800">บันทึกข้อมูลประวัติส่วนตัวเรียบร้อยแล้ว</h1>
                <p class="mt-2 text-sm text-stone-500">
                    เลขที่ใบสมัครอ้างอิงของคุณคือ <span class="font-semibold text-amber-700">APP-{{ str_pad($applicantId, 6, '0', STR_PAD_LEFT) }}</span>
                </p>
                <p class="mt-1 text-sm text-stone-500">
                    ขั้นตอนถัดไป (ประวัติการศึกษา, ความสามารถด้านภาษา, ประวัติการทำงาน ฯลฯ) อยู่ระหว่างการพัฒนา ทีมงานจะติดต่อกลับผ่านช่องทางที่ท่านให้ไว้
                </p>
                <a href="{{ url('/') }}" wire:navigate class="mt-6 inline-flex items-center gap-2 rounded-lg border border-stone-200 px-5 py-2.5 text-sm font-medium text-stone-700 transition hover:border-amber-500 hover:bg-amber-50 hover:text-amber-700">
                    กลับหน้าแรก
                </a>
            </div>
        @else

            {{-- Step progress --}}
            <div class="overflow-x-auto">
                <div class="flex min-w-max sm:min-w-0">
                    @foreach ([
                        'ประวัติส่วนตัว', 'ประวัติการศึกษา', 'ความสามารถด้านภาษา',
                        'ประวัติการทำงาน', 'ข้อมูลอื่นๆ & เอกสาร', 'วัน-เวลาทำงาน', 'ตรวจสอบข้อมูล',
                    ] as $index => $step)
                        <div class="flex-1 border-b-2 px-2 pb-2.5 text-center text-[11px] leading-tight sm:text-xs {{ $index === 0 ? 'border-amber-500 font-semibold text-amber-700' : 'border-stone-200 text-stone-400' }}">
                            {{ $step }}
                        </div>
                    @endforeach
                </div>
            </div>

            <form wire:submit="submit" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">ประวัติส่วนตัว <span class="font-normal text-stone-400">/ Personal Background</span></h1>
                <p class="mt-1 text-sm text-stone-500">กรอกข้อมูลให้ครบถ้วนและตรงตามความเป็นจริง</p>

                {{-- ตำแหน่งงานที่สมัคร --}}
                <x-form.section-title thai="ตำแหน่งงานที่สมัคร" english="Position" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.select label="ตำแหน่งที่ต้องการ" model="position_id" required placeholder="--เลือกตำแหน่งที่สนใจ--"
                        :options="$this->positions->pluck('title', 'id')->all()" />
                    <x-form.input label="เงินเดือนที่ต้องการ (บาท)" model="expected_salary" type="number" required placeholder="0" />
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
                    <x-form.input label="ส่วนสูง (ซม.)" model="height_cm" type="number" required placeholder="ส่วนสูง" />
                    <x-form.input label="น้ำหนัก (กก.)" model="weight_kg" type="number" required placeholder="น้ำหนัก" />

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
                        <span wire:loading.remove wire:target="submit">บันทึกข้อมูล</span>
                        <span wire:loading wire:target="submit">กำลังบันทึก...</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
