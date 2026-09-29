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

    // ภาษาที่เลือกไว้จากหน้า landing page (ผ่านคุกกี้ landingLang)
    public string $locale = 'th';

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
        $this->locale = request()->cookie('landingLang') === 'en' ? 'en' : 'th';

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

    // พจนานุกรมข้อความ UI สำหรับขั้นตอน "ประวัติส่วนตัว" (ยังไม่ครอบคลุมขั้นตอนอื่น)
    protected function tDict(): array
    {
        return [
            'step_personal' => ['th' => 'ประวัติส่วนตัว', 'en' => 'Personal Background'],
            'step_education' => ['th' => 'ประวัติการศึกษา', 'en' => 'Education Background'],
            'step_language' => ['th' => 'ความสามารถด้านภาษา', 'en' => 'Language Skills'],
            'step_work' => ['th' => 'ประวัติการทำงาน', 'en' => 'Employment History'],
            'step_other' => ['th' => 'ข้อมูลอื่นๆ & เอกสาร', 'en' => 'Other Info & Documents'],
            'step_schedule' => ['th' => 'วัน-เวลาทำงาน', 'en' => 'Work Schedule'],
            'step_review' => ['th' => 'ตรวจสอบข้อมูล', 'en' => 'Review'],

            'personal_subtitle' => ['th' => 'กรอกข้อมูลให้ครบถ้วนและตรงตามความเป็นจริง', 'en' => 'Please complete all fields accurately and truthfully'],

            'position_label' => ['th' => 'ตำแหน่งที่ต้องการ', 'en' => 'Desired Position'],
            'position_placeholder' => ['th' => '--เลือก--', 'en' => '--Select--'],
            'select_placeholder' => ['th' => '--เลือก--', 'en' => '--Select--'],
            'expected_salary' => ['th' => 'เงินเดือนที่ต้องการ (บาท)', 'en' => 'Expected Salary (THB)'],

            'date_of_birth' => ['th' => 'วันเดือนปีเกิด', 'en' => 'Date of Birth'],
            'age' => ['th' => 'อายุ', 'en' => 'Age'],
            'nationality' => ['th' => 'สัญชาติ', 'en' => 'Nationality'],
            'nationality_ph' => ['th' => 'เช่น ไทย', 'en' => 'e.g. Thai'],
            'ethnicity' => ['th' => 'เชื้อชาติ', 'en' => 'Race'],
            'ethnicity_ph' => ['th' => 'เช่น ไทย', 'en' => 'e.g. Thai'],
            'religion' => ['th' => 'ศาสนา', 'en' => 'Religion'],
            'religion_ph' => ['th' => 'เช่น พุทธ', 'en' => 'e.g. Buddhist'],
            'gender' => ['th' => 'เพศ', 'en' => 'Gender'],
            'gender_male' => ['th' => 'ชาย', 'en' => 'Male'],
            'gender_female' => ['th' => 'หญิง', 'en' => 'Female'],
            'gender_other' => ['th' => 'อื่นๆ', 'en' => 'Other'],
            'height' => ['th' => 'ส่วนสูง (ซม.)', 'en' => 'Height (cm)'],
            'height_ph' => ['th' => 'ส่วนสูง', 'en' => 'Height'],
            'weight' => ['th' => 'น้ำหนัก (กก.)', 'en' => 'Weight (kg)'],
            'weight_ph' => ['th' => 'น้ำหนัก', 'en' => 'Weight'],
            'has_congenital_disease' => ['th' => 'มีโรคประจำตัว', 'en' => 'Congenital Disease'],
            'yes' => ['th' => 'มี', 'en' => 'Yes'],
            'no' => ['th' => 'ไม่มี', 'en' => 'No'],
            'congenital_detail' => ['th' => 'รายละเอียดโรคประจำตัว', 'en' => 'Disease Details'],
            'has_criminal_record' => ['th' => 'มีประวัติอาชญากรรม', 'en' => 'Criminal Record'],
            'criminal_yes' => ['th' => 'เคยมี', 'en' => 'Yes'],
            'criminal_detail' => ['th' => 'รายละเอียดประวัติอาชญากรรม', 'en' => 'Criminal Record Details'],
            'country' => ['th' => 'ประเทศ', 'en' => 'Country'],
            'country_ph' => ['th' => 'เช่น ไทย', 'en' => 'e.g. Thailand'],
            'hometown' => ['th' => 'ภูมิลำเนา', 'en' => 'Hometown'],
            'hometown_ph' => ['th' => 'จังหวัด', 'en' => 'Province'],

            'email' => ['th' => 'Email', 'en' => 'Email'],
            'phone' => ['th' => 'เบอร์โทรศัพท์', 'en' => 'Phone Number'],
            'line_id' => ['th' => 'Line ID', 'en' => 'Line ID'],

            'national_id' => ['th' => 'เลขบัตรประชาชน', 'en' => 'National ID Number'],
            'national_id_back' => ['th' => 'เลขหลังบัตรประชาชน', 'en' => 'ID Back Number'],
            'national_id_back_ph' => ['th' => 'เลขหลังบัตร', 'en' => 'Back number'],
            'id_issue_date' => ['th' => 'วันออกบัตร', 'en' => 'Issue Date'],
            'id_expiry_date' => ['th' => 'บัตรหมดอายุ', 'en' => 'Expiry Date'],
            'current_address' => ['th' => 'ที่อยู่ปัจจุบันที่ติดต่อได้สะดวก', 'en' => 'Current Address'],
            'current_address_ph' => ['th' => 'บ้านเลขที่ ถนน ตำบล อำเภอ จังหวัด รหัสไปรษณีย์', 'en' => 'House No., Street, Subdistrict, District, Province, Postal Code'],

            'living_status' => ['th' => 'สถานะความเป็นอยู่', 'en' => 'Living Status'],
            'living_own' => ['th' => 'บ้านของตัวเอง', 'en' => 'Own home'],
            'living_rent' => ['th' => 'บ้านเช่า', 'en' => 'Rented home'],
            'living_parents' => ['th' => 'อยู่กับพ่อแม่', 'en' => 'With parents'],
            'living_other' => ['th' => 'อยู่กับผู้อื่น', 'en' => 'With others'],
            'marital_status' => ['th' => 'สถานะครอบครัว', 'en' => 'Marital Status'],
            'marital_single' => ['th' => 'โสด', 'en' => 'Single'],
            'marital_married' => ['th' => 'สมรส', 'en' => 'Married'],
            'marital_divorced' => ['th' => 'หย่าร้าง', 'en' => 'Divorced'],
            'marital_widowed' => ['th' => 'หม้าย', 'en' => 'Widowed'],
            'marital_separated' => ['th' => 'แยกกันอยู่', 'en' => 'Separated'],
            'marriage_registered' => ['th' => 'การจดทะเบียนสมรส', 'en' => 'Marriage Registration'],
            'registered' => ['th' => 'จดทะเบียน', 'en' => 'Registered'],
            'not_registered' => ['th' => 'ไม่จดทะเบียน', 'en' => 'Not registered'],
            'spouse_name' => ['th' => 'ชื่อคู่สมรส', 'en' => "Spouse's Name"],
            'spouse_name_ph' => ['th' => 'ชื่อ-นามสกุลคู่สมรส', 'en' => "Spouse's full name"],
            'spouse_occupation' => ['th' => 'อาชีพคู่สมรส', 'en' => "Spouse's Occupation"],
            'children_count' => ['th' => 'จำนวนบุตร', 'en' => 'Number of Children'],

            'father_name' => ['th' => 'ชื่อบิดา', 'en' => "Father's Name"],
            'father_name_ph' => ['th' => 'ชื่อ-นามสกุลบิดา', 'en' => "Father's full name"],
            'father_age' => ['th' => 'อายุบิดา (ปี)', 'en' => "Father's Age"],
            'father_occupation' => ['th' => 'อาชีพบิดา', 'en' => "Father's Occupation"],
            'father_status' => ['th' => 'สถานะบิดา', 'en' => "Father's Status"],
            'alive' => ['th' => 'มีชีวิตอยู่', 'en' => 'Alive'],
            'deceased' => ['th' => 'เสียชีวิตแล้ว', 'en' => 'Deceased'],
            'mother_name' => ['th' => 'ชื่อมารดา', 'en' => "Mother's Name"],
            'mother_name_ph' => ['th' => 'ชื่อ-นามสกุลมารดา', 'en' => "Mother's full name"],
            'mother_age' => ['th' => 'อายุมารดา (ปี)', 'en' => "Mother's Age"],
            'mother_occupation' => ['th' => 'อาชีพมารดา', 'en' => "Mother's Occupation"],
            'mother_status' => ['th' => 'สถานะมารดา', 'en' => "Mother's Status"],
            'occupation_ph' => ['th' => 'อาชีพ', 'en' => 'Occupation'],
            'siblings_count' => ['th' => 'จำนวนพี่น้อง (คน)', 'en' => 'No. of Brothers and Sisters'],
            'birth_order' => ['th' => 'เป็นบุตรคนที่', 'en' => 'Yourselves at No.'],

            'military_status' => ['th' => 'สถานะภาพทางการทหาร', 'en' => 'Military Status'],
            'military_exempted' => ['th' => 'ได้รับการยกเว้น', 'en' => 'Exempted'],
            'military_completed' => ['th' => 'ผ่านการเกณฑ์ทหารแล้ว', 'en' => 'Completed'],
            'military_awaiting' => ['th' => 'รอเข้ารับการเกณฑ์ทหาร', 'en' => 'Awaiting conscription'],
            'lese_majeste' => ['th' => 'มีประวัติหมิ่นพระบรมเดชานุภาพ', 'en' => 'Anti monarch record'],
            'lese_majeste_detail' => ['th' => 'รายละเอียด', 'en' => 'Details'],

            'back_home' => ['th' => 'กลับหน้าแรก', 'en' => 'Back to Home'],
            'back_step' => ['th' => 'ย้อนกลับ', 'en' => 'Back'],
            'save_continue' => ['th' => 'บันทึกและไปขั้นตอนถัดไป', 'en' => 'Save & Continue'],
            'saving' => ['th' => 'กำลังบันทึก...', 'en' => 'Saving...'],

            'education_subtitle' => ['th' => 'กรอกข้อมูลการศึกษาตามระดับที่ท่านสำเร็จ (เว้นว่างได้หากไม่มี)', 'en' => 'Fill in your education details by level completed (leave blank if not applicable)'],
            'institution_name' => ['th' => 'ชื่อสถาบัน', 'en' => 'Name of Institute'],
            'institution_name_ph' => ['th' => 'ชื่อสถาบัน', 'en' => 'Name of Institute'],
            'province_country' => ['th' => 'จังหวัด/ประเทศ', 'en' => 'Country'],
            'gpa' => ['th' => 'เกรดเฉลี่ย (GPA)', 'en' => 'GPA'],
            'gpa_ph' => ['th' => 'เช่น 3.50', 'en' => 'e.g. 3.50'],
            'start_year' => ['th' => 'เริ่มต้นปี (พ.ศ.)', 'en' => 'Start Year (B.E.)'],
            'start_year_ph' => ['th' => 'เช่น 2538', 'en' => 'e.g. 2538'],
            'end_year' => ['th' => 'ถึงปี (พ.ศ.)', 'en' => 'End Year (B.E.)'],
            'end_year_ph' => ['th' => 'เช่น 2541', 'en' => 'e.g. 2541'],
            'major' => ['th' => 'วิชาที่ศึกษา / วุฒิที่ได้รับ', 'en' => 'Course Taken-Completed'],
            'major_ph' => ['th' => 'เช่น สายวิทย์-คณิต', 'en' => 'e.g. Science-Math'],

            'language_subtitle' => ['th' => 'ประเมินระดับความสามารถทางภาษาตามความเป็นจริง โดยเรียงลำดับจากน้อย (0 = ไม่มีความสามารถเลย) ไปถึงมากที่สุด (5 = คล่องแคล่ว)', 'en' => 'Rate your language proficiency honestly, from lowest (0 = no ability) to highest (5 = fluent)'],
            'other_language_name' => ['th' => 'ระบุชื่อภาษา', 'en' => 'Specify Language'],
            'other_language_name_ph' => ['th' => 'เช่น ญี่ปุ่น, เกาหลี', 'en' => 'e.g. Japanese, Korean'],
            'speaking' => ['th' => 'การพูด', 'en' => 'Speaking'],
            'understanding' => ['th' => 'การเข้าใจ', 'en' => 'Understanding'],
            'reading' => ['th' => 'การอ่าน', 'en' => 'Reading'],
            'writing' => ['th' => 'การเขียน', 'en' => 'Writing'],
            'language_cert_label' => ['th' => 'ใบรับรองด้านภาษา (ถ้ามี)', 'en' => 'Language Certificate (if any)'],
            'language_cert_ph' => ['th' => 'เช่น TOEIC 750, HSK 4', 'en' => 'e.g. TOEIC 750, HSK 4'],

            'work_subtitle' => ['th' => 'กรอกประวัติการทำงานย้อนหลัง เรียงจากล่าสุด (เว้นว่างได้หากไม่มี) สูงสุด 4 สถานประกอบการ', 'en' => 'Fill in your work history from most recent, up to 4 employers (leave blank if not applicable)'],
            'work_note' => ['th' => '** หมายเหตุ ** ประสบการณ์ฝึกงานสามารถใส่ได้เช่นกัน', 'en' => '** Note ** Internship experience can also be included'],
            'establishment' => ['th' => 'สถานประกอบที่', 'en' => 'Establishment'],
            'company_name' => ['th' => 'ชื่อสถานประกอบการ', 'en' => 'Company Name'],
            'work_position' => ['th' => 'ตำแหน่ง', 'en' => 'Position'],
            'work_start_year' => ['th' => 'เริ่มต้นปี (พ.ศ.)', 'en' => 'Start Year (B.E.)'],
            'work_start_year_ph' => ['th' => 'เช่น 2560', 'en' => 'e.g. 2560'],
            'work_end_year' => ['th' => 'ถึงปี (พ.ศ.)', 'en' => 'End Year (B.E.)'],
            'work_end_year_ph' => ['th' => 'เช่น 2563', 'en' => 'e.g. 2563'],
            'work_duration' => ['th' => 'ระยะเวลาทั้งหมด', 'en' => 'Total Duration'],
            'supervisor_name' => ['th' => 'ชื่อหัวหน้างาน', 'en' => 'Leader'],
            'full_name_ph' => ['th' => 'ชื่อ-นามสกุล', 'en' => 'Full name'],
            'supervisor_phone' => ['th' => 'เบอร์โทร', 'en' => 'Phone Number'],
            'phone_ph' => ['th' => 'เบอร์โทร', 'en' => 'Phone number'],
            'last_salary' => ['th' => 'เงินเดือนล่าสุด (บาท)', 'en' => 'Last Salary (THB)'],
            'last_salary_ph' => ['th' => 'เช่น 50000', 'en' => 'e.g. 50000'],
            'resignation_reason' => ['th' => 'สาเหตุที่ออก', 'en' => 'Reasons of resignation'],
            'resignation_reason_ph' => ['th' => 'สาเหตุที่ออกจากงาน', 'en' => 'Reason for leaving the job'],
            'other_language_attr' => ['th' => 'ชื่อภาษาอื่นๆ', 'en' => 'Other Language Name'],

            'other_subtitle' => ['th' => 'ข้อมูลเพิ่มเติมสำหรับการพิจารณา', 'en' => 'Additional information for consideration'],
            'ref_name' => ['th' => 'ชื่อ - นามสกุล', 'en' => 'Full Name'],
            'ref_name_ph' => ['th' => 'ชื่อ - นามสกุล', 'en' => 'Full name'],
            'ref_relationship' => ['th' => 'ความสัมพันธ์', 'en' => 'Relationship'],
            'ref_relationship_ph' => ['th' => 'เช่น เพื่อน', 'en' => 'e.g. Friend'],
            'ref_workplace' => ['th' => 'สถานที่ทำงาน-ที่อยู่', 'en' => 'Workplace / Address'],
            'ref_workplace_ph' => ['th' => 'สถานที่ทำงาน / ที่อยู่', 'en' => 'Workplace / Address'],
            'ref_position' => ['th' => 'ตำแหน่ง', 'en' => 'Position'],
            'ref_phone' => ['th' => 'เบอร์โทร', 'en' => 'Phone Number'],
            'ref_phone_ph' => ['th' => 'เบอร์โทร', 'en' => 'Phone number'],

            'special_skills_label' => ['th' => 'ความรู้ความสามารถพิเศษ', 'en' => 'Special Skills / Abilities'],
            'guarantor_label' => ['th' => 'สามารถหาผู้ค้ำประกันได้หรือไม่', 'en' => 'Can you provide a gruarantor?'],
            'shift_label' => ['th' => 'สามารถทำงานเป็นกะหมุนเวียนได้หรือไม่', 'en' => 'Can you work shift by shift?'],
            'yes_able' => ['th' => 'ได้', 'en' => 'Yes'],
            'no_able' => ['th' => 'ไม่ได้', 'en' => 'No'],

            'debt_amount' => ['th' => 'เงินที่ชำระหนี้นอกระบบ (บาท/เดือน)', 'en' => 'Informal debt amounting to (Baht per month)'],
            'debt_interest' => ['th' => 'ดอกเบี้ย (ร้อยละ/เดือน)', 'en' => 'Interest Rate (Baht per month)'],
            'credit_debt' => ['th' => 'วงเงินบัตรเครดิต/บัตรกดเงินสด (บาท)', 'en' => 'You have a credit card/cash card (amount)'],
            'outstanding_balance' => ['th' => 'ยอดค้างชำระ (บาท)', 'en' => 'You have an outstanding balance totaling (Baht)'],

            'illness_label' => ['th' => 'ในช่วง 3 ปีที่ผ่านมา เคยเจ็บป่วยเข้าโรงพยาบาลหรือประสบอุบัติเหตุหรือไม่ (ถ้ามีระบุรายละเอียด)', 'en' => 'In the past 3 years, have you been hospitalized or had an accident? (please specify if any)'],
            'illness_ph' => ['th' => 'รายละเอียด (ถ้ามี)', 'en' => 'Details (if any)'],

            'known_person' => ['th' => 'บุคคลในบริษัทที่รู้จัก/แนะนำ', 'en' => 'Referrer in Company'],
            'start_date_label' => ['th' => 'วันที่พร้อมเริ่มงาน', 'en' => 'Available Start Date'],
            'source_channels_label' => ['th' => 'ช่องทางที่ทราบข่าวการรับสมัคร', 'en' => 'How did you hear about this job opening?'],
            'source_other_label' => ['th' => 'ระบุช่องทางอื่นๆ', 'en' => 'Specify Other Channel'],
            'source_other_ph' => ['th' => 'ระบุ', 'en' => 'Specify'],
            'referred_friend_label' => ['th' => 'ชื่อเพื่อน/ญาติที่แนะนำ', 'en' => "Referrer's Name (Friend/Relative)"],
            'source_friend_referral' => ['th' => 'เพื่อน/ญาติแนะนำ', 'en' => 'Friend/Relative Referral'],
            'source_other' => ['th' => 'อื่นๆ', 'en' => 'Other'],

            'schedule_subtitle' => ['th' => 'กรุณาอ่านรายละเอียดเวลาปฏิบัติงานและยินยอมข้อตกลงก่อนส่งใบสมัคร', 'en' => 'Please read the work schedule details and agree to the terms before submitting your application'],
            'non_production_title' => ['th' => 'กลุ่มพนักงานที่ไม่เกี่ยวข้องกับการผลิต', 'en' => 'Employee group that is not related to the production'],
            'non_production_bullet1' => ['th' => 'ในสัปดาห์ที่ 1, 3 ปฏิบัติงานวันจันทร์ ถึง วันศุกร์ และวันเสาร์ทำงานในรูปแบบ Work From Anywhere เวลาปฏิบัติงานครึ่งวันเช้า', 'en' => 'In weeks 1 and 3, work Monday to Friday, and Saturday is Work From Anywhere for a half-day morning shift'],
            'non_production_bullet2' => ['th' => 'ในสัปดาห์ที่ 2, 4 ปฏิบัติงานวันจันทร์ ถึง วันศุกร์ วันเสาร์เป็นวันหยุดประจำสัปดาห์', 'en' => 'In weeks 2 and 4, work Monday to Friday, with Saturday as the weekly day off'],
            'non_production_bullet3' => ['th' => 'ในสัปดาห์ที่ 5 ปฏิบัติงานวันเสาร์ (ทำกิจกรรม CSR) เวลาปฏิบัติงานตามข้อบังคับการทำงานของแต่ละบริษัท', 'en' => 'In week 5, work on Saturday (CSR activity), following each company\'s working regulations'],
            'production_title' => ['th' => 'กลุ่มพนักงานที่เกี่ยวข้องกับการผลิต', 'en' => 'Employee group that is related to the production'],
            'production_bullet1' => ['th' => 'ปฏิบัติงานวันจันทร์ ถึง วันเสาร์ เวลาปฏิบัติงานตามข้อบังคับการทำงานของแต่ละบริษัท (เลือกหยุดวันเสาร์ได้ 2 วันต่อเดือน)', 'en' => 'Work Monday to Saturday, following each company\'s working regulations (may choose 2 Saturdays off per month)'],
            'data_accuracy_consent' => ['th' => 'ข้าพเจ้าขอรับรองว่าข้อความข้างต้นทั้งหมดนี้เป็นความจริงทุกประการ หากข้อความตอนหนึ่งตอนใดไม่ตรงกับความเป็นจริง ข้าพเจ้าขอยอมรับว่าการว่าจ้างที่ตกลงนั้นเป็นอันโมฆะทันที และยินยอมให้เก็บ ใช้ เปิดเผย ตรวจสอบ ข้อมูลดังกล่าวได้ตลอดเวลาที่จำเป็นเพื่อการสมัครงาน และการเป็นพนักงานของบริษัทได้', 'en' => 'I certify that my answers are true and complete to the best of my knowledge. If this application leads to employment, I understand that false or misleading information in my application or interview may result in my release. I consent to the disclosure and verification of personal information at all times necessary for applying for a job and being an employee of the company.'],
            'privacy_policy_consent' => ['th' => 'ข้าพเจ้าได้อ่านนโยบายการคุ้มครองข้อมูลส่วนบุคคล สำหรับบุคลากรของบริษัท ข้าพเจ้ารับทราบและยินยอมให้เก็บ ใช้ เปิดเผย ตรวจสอบ ข้อมูลดังกล่าวได้ตลอดเวลาที่จำเป็นเพื่อการสมัครงาน และตลอดระยะเวลาการเป็นพนักงานของบริษัท', 'en' => 'I have read the personal data protection policy for company personnel. I acknowledge and consent to the disclosure and verification of personal information at all times necessary for applying for a job and throughout the period of being an employee of the company.'],
            'signature_label' => ['th' => 'ลงชื่อผู้สมัครงาน (พิมพ์ชื่อ-นามสกุล)', 'en' => 'Applicant Signature (Type full name)'],
            'continue_to_review' => ['th' => 'ไปที่หน้าตรวจสอบข้อมูล', 'en' => 'Go to Review Page'],
            'data_accuracy_consent_attr' => ['th' => 'การรับรองความถูกต้องของข้อมูล', 'en' => 'Data accuracy certification'],
            'privacy_policy_consent_attr' => ['th' => 'การยินยอมตามนโยบายคุ้มครองข้อมูลส่วนบุคคล', 'en' => 'Privacy policy consent'],
            'signature_name_attr' => ['th' => 'ลงชื่อผู้สมัครงาน', 'en' => 'Applicant Signature'],
        ];
    }

    public function tr(string $key): string
    {
        return $this->tDict()[$key][$this->locale] ?? $key;
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

        if ($this->locale === 'en') {
            return $years === 0 ? 'Less than 1 year' : "{$years} year".($years > 1 ? 's' : '');
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

    public function sourceChannelOptions(?string $locale = null): array
    {
        $locale ??= $this->locale;

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
            'เพื่อน/ญาติแนะนำ' => $this->tr('source_friend_referral'),
            'อื่นๆ' => $this->tr('source_other'),
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
        if ($this->locale === 'en') {
            return [
                'required' => ':attribute is required',
                'required_if' => ':attribute is required',
                'email' => ':attribute is invalid',
                'date' => ':attribute is invalid',
                'before' => ':attribute must be a date before today',
                'numeric' => ':attribute must be a number',
                'integer' => ':attribute must be a whole number',
                'in' => ':attribute is invalid',
                'boolean' => ':attribute is invalid',
                'max' => ':attribute is too long',
                'min' => ':attribute is invalid',
                'exists' => ':attribute is invalid',
                'string' => ':attribute is invalid',
            ];
        }

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
            'position_id' => $this->tr('position_label'),
            'expected_salary' => $this->tr('expected_salary'),
            'title_th' => 'คำนำหน้า (TH)',
            'first_name_th' => 'ชื่อ (TH)',
            'last_name_th' => 'นามสกุล (TH)',
            'nickname_th' => 'ชื่อเล่น (TH)',
            'title_en' => 'Title (ENG)',
            'first_name_en' => 'Name (ENG)',
            'last_name_en' => 'Surname (ENG)',
            'nickname_en' => 'Nickname (ENG)',
            'date_of_birth' => $this->tr('date_of_birth'),
            'nationality' => $this->tr('nationality'),
            'ethnicity' => $this->tr('ethnicity'),
            'religion' => $this->tr('religion'),
            'gender' => $this->tr('gender'),
            'height_cm' => $this->tr('height'),
            'weight_kg' => $this->tr('weight'),
            'country' => $this->tr('country'),
            'hometown' => $this->tr('hometown'),
            'has_congenital_disease' => $this->tr('has_congenital_disease'),
            'congenital_disease_detail' => $this->tr('congenital_detail'),
            'has_criminal_record' => $this->tr('has_criminal_record'),
            'criminal_record_detail' => $this->tr('criminal_detail'),
            'email' => $this->tr('email'),
            'phone' => $this->tr('phone'),
            'national_id_number' => $this->tr('national_id'),
            'current_address' => $this->tr('current_address'),
            'living_status' => $this->tr('living_status'),
            'marital_status' => $this->tr('marital_status'),
            'marriage_registered' => $this->tr('marriage_registered'),
            'spouse_name' => $this->tr('spouse_name'),
            'military_status' => $this->tr('military_status'),
            'has_lese_majeste_record' => $this->tr('lese_majeste'),
            'lese_majeste_detail' => $this->tr('lese_majeste_detail'),
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
            'speaking_level' => $this->tr('speaking'),
            'understanding_level' => $this->tr('understanding'),
            'reading_level' => $this->tr('reading'),
            'writing_level' => $this->tr('writing'),
        ];
        $attributes = ['languages.other.custom_language_name' => $this->tr('other_language_attr')];
        foreach ($this->languageKeys() as $lang => $labels) {
            foreach ($skillLabels as $skill => $skillLabel) {
                $attributes["languages.{$lang}.{$skill}"] = "{$labels[$this->locale]} - {$skillLabel}";
            }
        }

        $messages = $this->locale === 'en'
            ? [
                'required' => ':attribute is required',
                'required_with' => ':attribute is required when a language name is specified',
                'integer' => ':attribute must be a number',
                'between' => ':attribute must be between 0 and 5',
            ]
            : [
                'required' => ':attribute จำเป็นต้องกรอก',
                'required_with' => ':attribute จำเป็นต้องกรอกเมื่อระบุชื่อภาษา',
                'integer' => ':attribute ต้องเป็นตัวเลข',
                'between' => ':attribute ต้องอยู่ระหว่าง 0 ถึง 5',
            ];

        $data = $this->validate($rules, $messages, $attributes);

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

            $attributes["workHistories.{$i}.company_name"] = $this->locale === 'en'
                ? "{$this->tr('company_name')} {$i}"
                : "{$this->tr('company_name')}ที่ {$i}";
            $attributes["workHistories.{$i}.position"] = $this->locale === 'en'
                ? "{$this->tr('work_position')} {$i}"
                : "{$this->tr('work_position')}ที่ {$i}";
        }

        $messages = $this->locale === 'en'
            ? [
                'required_with' => ':attribute is required',
                'integer' => ':attribute must be a number',
                'digits' => ':attribute must have 4 digits',
                'numeric' => ':attribute must be a number',
            ]
            : [
                'required_with' => ':attribute จำเป็นต้องกรอก',
                'integer' => ':attribute ต้องเป็นตัวเลข',
                'digits' => ':attribute ต้องมี 4 หลัก',
                'numeric' => ':attribute ต้องเป็นตัวเลข',
            ];

        $data = $this->validate($rules, $messages, $attributes);

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

        $attributes = $this->locale === 'en'
            ? [
                'references.1.name' => 'Full Name (Person 1)',
                'references.1.relationship' => 'Relationship (Person 1)',
                'references.1.workplace_address' => 'Workplace / Address (Person 1)',
                'references.1.phone' => 'Phone Number (Person 1)',
                'references.2.relationship' => 'Relationship (Person 2)',
                'references.2.phone' => 'Phone Number (Person 2)',
            ]
            : [
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
            $attributes["documents.{$type}"] = $meta[$this->locale];
        }

        $messages = $this->locale === 'en'
            ? [
                'required' => ':attribute is required',
                'required_with' => ':attribute is required',
                'numeric' => ':attribute must be a number',
                'date' => ':attribute is invalid',
                'mimes' => ':attribute must be a JPG, PNG, or PDF file only',
                'max' => ':attribute is too large (max 5MB)',
            ]
            : [
                'required' => ':attribute จำเป็นต้องกรอก',
                'required_with' => ':attribute จำเป็นต้องกรอก',
                'numeric' => ':attribute ต้องเป็นตัวเลข',
                'date' => ':attribute ไม่ถูกต้อง',
                'mimes' => ':attribute ต้องเป็นไฟล์ JPG, PNG หรือ PDF เท่านั้น',
                'max' => ':attribute มีขนาดใหญ่เกินไป (สูงสุด 5MB)',
            ];

        $data = $this->validate($rules, $messages, $attributes);

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
        $messages = $this->locale === 'en'
            ? [
                'accepted' => ':attribute must be accepted before the application can be submitted',
                'required' => ':attribute is required',
            ]
            : [
                'accepted' => ':attribute ต้องกดยอมรับก่อนจึงจะส่งใบสมัครได้',
                'required' => ':attribute จำเป็นต้องกรอก',
            ];

        $data = $this->validate([
            'data_accuracy_consent' => ['accepted'],
            'privacy_policy_consent' => ['accepted'],
            'signature_name' => ['required', 'string', 'max:255'],
        ], $messages, [
            'data_accuracy_consent' => $this->tr('data_accuracy_consent_attr'),
            'privacy_policy_consent' => $this->tr('privacy_policy_consent_attr'),
            'signature_name' => $this->tr('signature_name_attr'),
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
                    $this->tr('step_personal'), $this->tr('step_education'), $this->tr('step_language'),
                    $this->tr('step_work'), $this->tr('step_other'), $this->tr('step_schedule'), $this->tr('step_review'),
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
                <h1 class="text-xl font-semibold text-stone-800">{{ $this->tr('step_personal') }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $this->tr('personal_subtitle') }}</p>

                {{-- ตำแหน่งงานที่สมัคร --}}
                <x-form.section-title thai="ตำแหน่งงานที่สมัคร" english="Position" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.select :label="$this->tr('position_label')" model="position_id" required :placeholder="$this->tr('position_placeholder')"
                        :options="$this->positions->pluck('title', 'id')->all()" />
                    <x-form.input :label="$this->tr('expected_salary')" model="expected_salary" type="number" step="0.01" required placeholder="0" />
                </div>

                {{-- ข้อมูลส่วนตัว --}}
                <x-form.section-title thai="ข้อมูลส่วนตัว" english="Personal information" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-form.select label="คำนำหน้า (TH)" model="title_th" required :locale="$locale"
                        :options="['นาย' => 'นาย', 'นาง' => 'นาง', 'นางสาว' => 'นางสาว']" />
                    <x-form.input label="ชื่อ (TH)" model="first_name_th" required placeholder="ชื่อ" />
                    <x-form.input label="นามสกุล (TH)" model="last_name_th" required placeholder="นามสกุล" />
                    <x-form.input label="ชื่อเล่น (TH)" model="nickname_th" required placeholder="ชื่อเล่น" />

                    <x-form.select label="Title (ENG)" model="title_en" required :locale="$locale"
                        :options="['Mr.' => 'Mr.', 'Mrs.' => 'Mrs.', 'Ms.' => 'Ms.']" />
                    <x-form.input label="Name (ENG)" model="first_name_en" required placeholder="Name" />
                    <x-form.input label="Surname (ENG)" model="last_name_en" required placeholder="Surname" />
                    <x-form.input label="Nickname (ENG)" model="nickname_en" required placeholder="Nickname" />

                    <x-form.input :label="$this->tr('date_of_birth')" model="date_of_birth" type="date" required live />
                    <x-form.input :label="$this->tr('age')" model="age" value="{{ $this->age }}" disabled placeholder="-" />
                    <x-form.input :label="$this->tr('nationality')" model="nationality" required :placeholder="$this->tr('nationality_ph')" />
                    <x-form.input :label="$this->tr('ethnicity')" model="ethnicity" required :placeholder="$this->tr('ethnicity_ph')" />

                    <x-form.input :label="$this->tr('religion')" model="religion" required :placeholder="$this->tr('religion_ph')" />
                    <x-form.select :label="$this->tr('gender')" model="gender" required :placeholder="$this->tr('select_placeholder')"
                        :options="['male' => $this->tr('gender_male'), 'female' => $this->tr('gender_female'), 'other' => $this->tr('gender_other')]" />
                    <x-form.input :label="$this->tr('height')" model="height_cm" type="number" step="0.1" required :placeholder="$this->tr('height_ph')" />
                    <x-form.input :label="$this->tr('weight')" model="weight_kg" type="number" step="0.1" required :placeholder="$this->tr('weight_ph')" />

                    <x-form.radio-group :label="$this->tr('has_congenital_disease')" model="has_congenital_disease" required live
                        :options="['1' => $this->tr('yes'), '0' => $this->tr('no')]" />
                    @if ($has_congenital_disease === '1')
                        <x-form.input :label="$this->tr('congenital_detail')" model="congenital_disease_detail" required wrapper-class="sm:col-span-2" />
                    @endif

                    <x-form.radio-group :label="$this->tr('has_criminal_record')" model="has_criminal_record" required live
                        :options="['1' => $this->tr('criminal_yes'), '0' => $this->tr('no')]" />
                    @if ($has_criminal_record === '1')
                        <x-form.input :label="$this->tr('criminal_detail')" model="criminal_record_detail" required wrapper-class="sm:col-span-2" />
                    @endif

                    <x-form.input :label="$this->tr('country')" model="country" required :placeholder="$this->tr('country_ph')" />
                    <x-form.input :label="$this->tr('hometown')" model="hometown" required :placeholder="$this->tr('hometown_ph')" wrapper-class="sm:col-span-2 lg:col-span-4" />
                </div>

                {{-- ติดต่อ --}}
                <x-form.section-title thai="ช่องทางติดต่อ" english="Contact" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <x-form.input :label="$this->tr('email')" model="email" type="email" required placeholder="you@example.com" />
                    <x-form.input :label="$this->tr('phone')" model="phone" required placeholder="08x-xxx-xxxx" />
                    <x-form.input :label="$this->tr('line_id')" model="line_id" placeholder="Line ID" />
                    <x-form.input label="Facebook" model="facebook" placeholder="Facebook" />
                    <x-form.input label="Instagram" model="instagram" placeholder="Instagram" />
                    <x-form.input label="Twitter" model="twitter" placeholder="Twitter" />
                </div>

                {{-- บัตรประชาชน & ที่อยู่ --}}
                <x-form.section-title thai="บัตรประชาชน & ที่อยู่" english="ID card & Address" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-form.input :label="$this->tr('national_id')" model="national_id_number" required placeholder="x-xxxx-xxxxx-xx-x" />
                    <x-form.input :label="$this->tr('national_id_back')" model="national_id_back_number" :placeholder="$this->tr('national_id_back_ph')" />
                    <x-form.input :label="$this->tr('id_issue_date')" model="id_issue_date" type="date" />
                    <x-form.input :label="$this->tr('id_expiry_date')" model="id_expiry_date" type="date" />
                </div>
                <div class="mt-5">
                    <x-form.textarea :label="$this->tr('current_address')" model="current_address" required :placeholder="$this->tr('current_address_ph')" />
                </div>

                {{-- สถานะความเป็นอยู่ & ครอบครัว --}}
                <x-form.section-title thai="สถานะความเป็นอยู่ & ครอบครัว" english="Living Status & Family" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.radio-group :label="$this->tr('living_status')" model="living_status" required
                        :options="['own_home' => $this->tr('living_own'), 'rent_home' => $this->tr('living_rent'), 'with_parents' => $this->tr('living_parents'), 'with_other' => $this->tr('living_other')]" />
                    <x-form.radio-group :label="$this->tr('marital_status')" model="marital_status" required live
                        :options="['single' => $this->tr('marital_single'), 'married' => $this->tr('marital_married'), 'divorced' => $this->tr('marital_divorced'), 'widowed' => $this->tr('marital_widowed'), 'separated' => $this->tr('marital_separated')]" />
                </div>

                @if ($marital_status === 'married')
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <x-form.radio-group :label="$this->tr('marriage_registered')" model="marriage_registered" required
                            :options="['1' => $this->tr('registered'), '0' => $this->tr('not_registered')]" />
                        <x-form.input :label="$this->tr('spouse_name')" model="spouse_name" required :placeholder="$this->tr('spouse_name_ph')" />
                        <x-form.input :label="$this->tr('spouse_occupation')" model="spouse_occupation" :placeholder="$this->tr('occupation_ph')" />
                        <x-form.input :label="$this->tr('children_count')" model="children_count" type="number" placeholder="0" />
                    </div>
                @endif

                {{-- บิดามารดาและพี่น้อง --}}
                <x-form.section-title thai="ข้อมูลบิดามารดาและพี่น้อง" english="Parent & Sibling Information" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-form.input :label="$this->tr('father_name')" model="father_name" :placeholder="$this->tr('father_name_ph')" />
                    <x-form.input :label="$this->tr('father_age')" model="father_age" type="number" placeholder="0" />
                    <x-form.input :label="$this->tr('father_occupation')" model="father_occupation" :placeholder="$this->tr('occupation_ph')" />
                    <x-form.radio-group :label="$this->tr('father_status')" model="father_alive"
                        :options="['1' => $this->tr('alive'), '0' => $this->tr('deceased')]" />

                    <x-form.input :label="$this->tr('mother_name')" model="mother_name" :placeholder="$this->tr('mother_name_ph')" />
                    <x-form.input :label="$this->tr('mother_age')" model="mother_age" type="number" placeholder="0" />
                    <x-form.input :label="$this->tr('mother_occupation')" model="mother_occupation" :placeholder="$this->tr('occupation_ph')" />
                    <x-form.radio-group :label="$this->tr('mother_status')" model="mother_alive"
                        :options="['1' => $this->tr('alive'), '0' => $this->tr('deceased')]" />

                    <x-form.input :label="$this->tr('siblings_count')" model="siblings_count" type="number" placeholder="0" />
                    <x-form.input :label="$this->tr('birth_order')" model="birth_order" type="number" placeholder="0" />
                </div>

                {{-- ทหาร & ประวัติ --}}
                <x-form.section-title thai="สถานะภาพทางการทหาร & ประวัติ" english="Military Service" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.radio-group :label="$this->tr('military_status')" model="military_status" required
                        :options="['exempted' => $this->tr('military_exempted'), 'completed' => $this->tr('military_completed'), 'awaiting_conscription' => $this->tr('military_awaiting')]" />
                    <x-form.radio-group :label="$this->tr('lese_majeste')" model="has_lese_majeste_record" required live
                        :options="['1' => $this->tr('yes'), '0' => $this->tr('no')]" />
                </div>
                @if ($has_lese_majeste_record === '1')
                    <div class="mt-5">
                        <x-form.input :label="$this->tr('lese_majeste_detail')" model="lese_majeste_detail" required />
                    </div>
                @endif

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <a href="{{ url('/') }}" wire:navigate class="text-sm font-medium text-stone-500 hover:text-stone-700">
                        &larr; {{ $this->tr('back_home') }}
                    </a>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitPersonal">{{ $this->tr('save_continue') }}</span>
                        <span wire:loading wire:target="submitPersonal">{{ $this->tr('saving') }}</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'education')
            <form wire:submit="submitEducation" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">{{ $this->tr('step_education') }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $this->tr('education_subtitle') }}</p>

                @foreach ($this->educationLevels() as $level => $levelLabel)
                    <x-form.section-title :thai="$levelLabel['th']" :english="$levelLabel['en']" :locale="$locale" />
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <x-form.input :label="$this->tr('institution_name')" model="education.{{ $level }}.institution_name" :placeholder="$this->tr('institution_name_ph')" wrapper-class="sm:col-span-2" />
                        <x-form.input :label="$this->tr('province_country')" model="education.{{ $level }}.province_country" :placeholder="$this->tr('province_country')" />
                        <x-form.input :label="$this->tr('gpa')" model="education.{{ $level }}.gpa" type="number" step="0.01" :placeholder="$this->tr('gpa_ph')" />

                        <x-form.input :label="$this->tr('start_year')" model="education.{{ $level }}.start_year" type="number" :placeholder="$this->tr('start_year_ph')" />
                        <x-form.input :label="$this->tr('end_year')" model="education.{{ $level }}.end_year" type="number" :placeholder="$this->tr('end_year_ph')" />
                        <x-form.input :label="$this->tr('major')" model="education.{{ $level }}.major" :placeholder="$this->tr('major_ph')" wrapper-class="sm:col-span-2" />
                    </div>
                @endforeach

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'personal')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; {{ $this->tr('back_step') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitEducation">{{ $this->tr('save_continue') }}</span>
                        <span wire:loading wire:target="submitEducation">{{ $this->tr('saving') }}</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'language')
            @php
                $levelOptions = ['0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'];
            @endphp
            <form wire:submit="submitLanguage" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">{{ $this->tr('step_language') }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $this->tr('language_subtitle') }}</p>

                @foreach ($this->languageKeys() as $lang => $langLabel)
                    <x-form.section-title :thai="$langLabel['th']" :english="$langLabel['en']" :locale="$locale" />

                    @if ($lang === 'other')
                        <div class="mb-5">
                            <x-form.input :label="$this->tr('other_language_name')" model="languages.other.custom_language_name" :placeholder="$this->tr('other_language_name_ph')" />
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.radio-group :label="$this->tr('speaking')" model="languages.{{ $lang }}.speaking_level" :required="$lang !== 'other'" :options="$levelOptions" />
                        <x-form.radio-group :label="$this->tr('understanding')" model="languages.{{ $lang }}.understanding_level" :required="$lang !== 'other'" :options="$levelOptions" />
                        <x-form.radio-group :label="$this->tr('reading')" model="languages.{{ $lang }}.reading_level" :required="$lang !== 'other'" :options="$levelOptions" />
                        <x-form.radio-group :label="$this->tr('writing')" model="languages.{{ $lang }}.writing_level" :required="$lang !== 'other'" :options="$levelOptions" />
                    </div>
                @endforeach

                <x-form.section-title thai="ใบรับรองด้านภาษา" english="Certificate of Languages" :locale="$locale" />
                <x-form.input :label="$this->tr('language_cert_label')" model="language_certificate_note" :placeholder="$this->tr('language_cert_ph')" />

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'education')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; {{ $this->tr('back_step') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitLanguage">{{ $this->tr('save_continue') }}</span>
                        <span wire:loading wire:target="submitLanguage">{{ $this->tr('saving') }}</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'work')
            <form wire:submit="submitWork" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">{{ $this->tr('step_work') }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $this->tr('work_subtitle') }}</p>
                <p class="mt-1 text-xs text-stone-400">{{ $this->tr('work_note') }}</p>

                @for ($i = 1; $i <= 4; $i++)
                    <x-form.section-title thai="{{ $this->tr('establishment') }} {{ $i }}" english="Establishment {{ $i }}" :locale="$locale" />

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.input :label="$this->tr('company_name')" model="workHistories.{{ $i }}.company_name" :placeholder="$this->tr('company_name')" />
                        <x-form.input :label="$this->tr('work_position')" model="workHistories.{{ $i }}.position" :placeholder="$this->tr('work_position')" />
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <x-form.input :label="$this->tr('work_start_year')" model="workHistories.{{ $i }}.start_year" type="number" :placeholder="$this->tr('work_start_year_ph')" live />
                        <x-form.input :label="$this->tr('work_end_year')" model="workHistories.{{ $i }}.end_year" type="number" :placeholder="$this->tr('work_end_year_ph')" live />
                        <x-form.input :label="$this->tr('work_duration')" model="work_duration_{{ $i }}" value="{{ $this->workDuration($i) }}" disabled placeholder="-" />
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <x-form.input :label="$this->tr('supervisor_name')" model="workHistories.{{ $i }}.supervisor_name" :placeholder="$this->tr('full_name_ph')" />
                        <x-form.input :label="$this->tr('supervisor_phone')" model="workHistories.{{ $i }}.supervisor_phone" :placeholder="$this->tr('phone_ph')" />
                        <x-form.input :label="$this->tr('last_salary')" model="workHistories.{{ $i }}.last_salary" type="number" step="0.01" :placeholder="$this->tr('last_salary_ph')" />
                    </div>

                    <div class="mt-5">
                        <x-form.textarea :label="$this->tr('resignation_reason')" model="workHistories.{{ $i }}.resignation_reason" :placeholder="$this->tr('resignation_reason_ph')" />
                    </div>
                @endfor

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'language')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; {{ $this->tr('back_step') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitWork">{{ $this->tr('save_continue') }}</span>
                        <span wire:loading wire:target="submitWork">{{ $this->tr('saving') }}</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'other')
            <form wire:submit="submitOther" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">{{ $this->tr('step_other') }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $this->tr('other_subtitle') }}</p>

                @for ($i = 1; $i <= 2; $i++)
                    <x-form.section-title
                        thai="บุคคลคนที่ {{ $i }} ที่ไม่ใช่ญาติซึ่งทราบประวัติของท่านและบริษัทสามารถสอบถามได้"
                        english="Persons other than relatives can be contacted"
                        :locale="$locale" />
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.input :label="$this->tr('ref_name')" model="references.{{ $i }}.name" :required="$i === 1" :placeholder="$this->tr('ref_name_ph')" />
                        <x-form.input :label="$this->tr('ref_relationship')" model="references.{{ $i }}.relationship" :required="$i === 1" :placeholder="$this->tr('ref_relationship_ph')" />
                    </div>
                    <div class="mt-5">
                        <x-form.input :label="$this->tr('ref_workplace')" model="references.{{ $i }}.workplace_address" :required="$i === 1" :placeholder="$this->tr('ref_workplace_ph')" />
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-form.input :label="$this->tr('ref_position')" model="references.{{ $i }}.position" :placeholder="$this->tr('ref_position')" />
                        <x-form.input :label="$this->tr('ref_phone')" model="references.{{ $i }}.phone" :required="$i === 1" :placeholder="$this->tr('ref_phone_ph')" />
                    </div>
                @endfor

                <x-form.section-title thai="ความสามารถพิเศษ & เงื่อนไขการทำงาน" english="Special ability & Working conditions" :locale="$locale" />
                <div class="mb-5">
                    <x-form.textarea :label="$this->tr('special_skills_label')" model="special_skills" :placeholder="$this->tr('special_skills_label')" />
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-form.radio-group :label="$this->tr('guarantor_label')" model="guarantor_required_ok" :options="['1' => $this->tr('yes_able'), '0' => $this->tr('no_able')]" />
                    <x-form.radio-group :label="$this->tr('shift_label')" model="shift_work_ok" :options="['1' => $this->tr('yes_able'), '0' => $this->tr('no_able')]" />
                </div>

                <x-form.section-title thai="ข้อมูลด้านการเงิน" english="Financial Information" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-form.input :label="$this->tr('debt_amount')" model="informal_debt_amount" type="number" step="0.01" placeholder="0" />
                    <x-form.input :label="$this->tr('debt_interest')" model="informal_debt_interest_rate" type="number" step="0.01" placeholder="0" />
                    <x-form.input :label="$this->tr('credit_debt')" model="credit_card_debt_amount" type="number" step="0.01" placeholder="0" />
                    <x-form.input :label="$this->tr('outstanding_balance')" model="total_outstanding_balance" type="number" step="0.01" placeholder="0" />
                </div>

                <x-form.section-title thai="ประวัติสุขภาพ" english="Health History" :locale="$locale" />
                <x-form.textarea :label="$this->tr('illness_label')" model="illness_or_accident_history" :placeholder="$this->tr('illness_ph')" />

                <x-form.section-title thai="ข้อมูลอื่นๆ" english="Additional information" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <x-form.input :label="$this->tr('known_person')" model="known_person_in_company" :placeholder="$this->tr('full_name_ph')" />
                    <x-form.input :label="$this->tr('ref_relationship')" model="known_person_relation" :placeholder="$this->tr('ref_relationship')" />
                    <x-form.input :label="$this->tr('start_date_label')" model="available_start_date" type="date" />
                </div>
                <div class="mt-5">
                    <x-form.checkbox-group :label="$this->tr('source_channels_label')" model="source_channels" :options="$this->sourceChannelOptions($locale)" live />
                </div>
                @if (in_array('อื่นๆ', $source_channels) || in_array('เพื่อน/ญาติแนะนำ', $source_channels))
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @if (in_array('อื่นๆ', $source_channels))
                            <x-form.input :label="$this->tr('source_other_label')" model="source_channel_other" :placeholder="$this->tr('source_other_ph')" />
                        @endif
                        @if (in_array('เพื่อน/ญาติแนะนำ', $source_channels))
                            <x-form.input :label="$this->tr('referred_friend_label')" model="referred_friend_name" :placeholder="$this->tr('full_name_ph')" />
                        @endif
                    </div>
                @endif

                <x-form.section-title thai="เอกสารแนบ" english="Attachment (JPG, PNG, PDF)" :locale="$locale" />
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach ($this->documentTypes() as $type => $meta)
                        <x-form.file
                            :label="$meta[$locale]"
                            model="documents.{{ $type }}"
                            :accept="$meta['accept']"
                            :required="$meta['required']"
                            :locale="$locale"
                        />
                    @endforeach
                </div>

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'work')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; {{ $this->tr('back_step') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitOther">{{ $this->tr('save_continue') }}</span>
                        <span wire:loading wire:target="submitOther">{{ $this->tr('saving') }}</span>
                    </button>
                </div>
            </form>
        @elseif ($step === 'schedule')
            <form wire:submit="submitSchedule" class="mt-4 rounded-2xl bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.06)] ring-1 ring-black/5 sm:p-10">
                <h1 class="text-xl font-semibold text-stone-800">{{ $this->tr('step_schedule') }}</h1>
                <p class="mt-1 text-sm text-stone-500">{{ $this->tr('schedule_subtitle') }}</p>

                <div class="mt-6 space-y-4">
                    <div class="rounded-xl bg-amber-50/60 p-5">
                        <h3 class="text-sm font-semibold text-amber-700">{{ $this->tr('non_production_title') }}</h3>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-stone-600">
                            <li>{{ $this->tr('non_production_bullet1') }}</li>
                            <li>{{ $this->tr('non_production_bullet2') }}</li>
                            <li>{{ $this->tr('non_production_bullet3') }}</li>
                        </ul>
                    </div>

                    <div class="rounded-xl bg-amber-50/60 p-5">
                        <h3 class="text-sm font-semibold text-amber-700">{{ $this->tr('production_title') }}</h3>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-stone-600">
                            <li>{{ $this->tr('production_bullet1') }}</li>
                        </ul>
                    </div>
                </div>

                <x-form.section-title thai="ข้อตกลงและการยินยอม" english="Consent" :locale="$locale" />
                <div class="space-y-4">
                    <x-form.checkbox model="data_accuracy_consent" required>
                        {{ $this->tr('data_accuracy_consent') }}
                    </x-form.checkbox>

                    <x-form.checkbox model="privacy_policy_consent" required>
                        {{ $this->tr('privacy_policy_consent') }}
                    </x-form.checkbox>
                </div>

                <div class="mt-6 max-w-sm">
                    <x-form.input :label="$this->tr('signature_label')" model="signature_name" required :placeholder="$this->tr('full_name_ph')" />
                </div>

                <div class="mt-10 flex flex-col-reverse items-center justify-between gap-4 border-t border-stone-100 pt-6 sm:flex-row">
                    <button
                        type="button"
                        wire:click="$set('step', 'other')"
                        class="text-sm font-medium text-stone-500 hover:text-stone-700"
                    >
                        &larr; {{ $this->tr('back_step') }}
                    </button>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="submitSchedule">{{ $this->tr('continue_to_review') }}</span>
                        <span wire:loading wire:target="submitSchedule">{{ $this->tr('saving') }}</span>
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
