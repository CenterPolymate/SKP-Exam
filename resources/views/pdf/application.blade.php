<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบสมัครงาน APP-{{ str_pad($applicant->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @font-face {
            font-family: 'Sarabun';
            font-weight: normal;
            font-style: normal;
            src: url('{{ str_replace('\\', '/', storage_path('fonts/Sarabun-Regular.ttf')) }}') format('truetype');
        }
        @font-face {
            font-family: 'Sarabun';
            font-weight: bold;
            font-style: normal;
            src: url('{{ str_replace('\\', '/', storage_path('fonts/Sarabun-Bold.ttf')) }}') format('truetype');
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 11px;
            color: #292524;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #b45309;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header table { width: 100%; }
        .company-name { font-size: 18px; font-weight: bold; color: #b45309; }
        .doc-title { font-size: 14px; font-weight: bold; margin-top: 2px; }
        .doc-sub { font-size: 10px; color: #78716c; }
        .app-ref { text-align: right; font-size: 11px; }
        .app-ref .ref-no { font-size: 13px; font-weight: bold; color: #b45309; }
        .photo-box {
            width: 90px; height: 110px; border: 1px solid #d6d3d1;
            text-align: center; vertical-align: middle; font-size: 9px; color: #a8a29e;
        }
        .photo-box img { width: 88px; height: 108px; }

        .section-title {
            background-color: #fef3c7; color: #92400e; font-weight: bold;
            font-size: 12px; padding: 5px 8px; margin-top: 14px; margin-bottom: 6px;
        }
        .section-title .en { font-weight: normal; color: #a16207; font-size: 10px; }

        table.fields { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.fields td {
            padding: 3px 6px; vertical-align: top; border-bottom: 1px solid #f1f0ee;
        }
        table.fields td.label { width: 26%; color: #78716c; font-size: 10px; }
        table.fields td.value { width: 24%; font-size: 11px; font-weight: bold; }

        .sub-block { border: 1px solid #e7e5e4; padding: 8px; margin-bottom: 8px; }
        .sub-block-title { font-weight: bold; color: #b45309; font-size: 11px; margin-bottom: 4px; }

        table.docs { width: 100%; border-collapse: collapse; }
        table.docs td { padding: 3px 6px; font-size: 10px; border-bottom: 1px solid #f1f0ee; }
        .doc-yes { color: #15803d; font-weight: bold; }
        .doc-no { color: #a8a29e; }

        .consent-box { border: 1px solid #e7e5e4; padding: 8px; margin-top: 6px; font-size: 9.5px; color: #57534e; }
        .consent-box .checked { color: #15803d; font-weight: bold; }

        .signature-block { margin-top: 20px; width: 100%; }
        .signature-block td { width: 50%; text-align: center; font-size: 10px; padding-top: 24px; }
        .signature-line { border-top: 1px solid #78716c; padding-top: 4px; width: 70%; margin: 0 auto; }

        .footer { margin-top: 16px; font-size: 8.5px; color: #a8a29e; text-align: center; }
    </style>
</head>
<body>

    @php
        $genderLabels = ['male' => 'ชาย / Male', 'female' => 'หญิง / Female', 'other' => 'อื่นๆ / Other'];
        $livingStatusLabels = [
            'own_home' => 'บ้านของตัวเอง / Own home', 'rent_home' => 'บ้านเช่า / Rent home',
            'with_parents' => 'อยู่กับพ่อแม่ / Live with Parents', 'with_other' => 'อยู่กับผู้อื่น / Live with other',
        ];
        $maritalStatusLabels = [
            'single' => 'โสด / Single', 'married' => 'สมรส / Married', 'divorced' => 'หย่าร้าง / Divorced',
            'widowed' => 'หม้าย / Widowed', 'separated' => 'แยกกันอยู่ / Separated',
        ];
        $militaryStatusLabels = [
            'exempted' => 'ได้รับการยกเว้น / Exempted', 'completed' => 'ผ่านการเกณฑ์ทหารแล้ว / Completed',
            'awaiting_conscription' => 'รอเข้ารับการเกณฑ์ทหาร / Awaiting conscription',
        ];
        $degreeLevelLabels = [
            'junior_high' => 'มัธยมศึกษาตอนต้น', 'senior_high_vocational_cert' => 'มัธยมศึกษาตอนปลาย/ปวช.',
            'diploma_high_vocational' => 'อนุปริญญา/ปวส.', 'vocational' => 'วิชาชีพ',
            'bachelor' => 'ปริญญาตรี', 'master' => 'ปริญญาโท', 'other' => 'อื่นๆ',
        ];
        $languageLabels = ['thai' => 'ไทย / Thai', 'english' => 'อังกฤษ / English', 'chinese' => 'จีน / Chinese', 'other' => 'อื่นๆ'];
        $documentTypeLabels = [
            'photo' => 'รูปถ่ายหน้าตรง 1 นิ้ว', 'education_certificate' => 'ใบรับรองการศึกษา',
            'house_registration' => 'สำเนาทะเบียนบ้าน', 'id_card_copy' => 'สำเนาบัตรประชาชน',
            'military_certificate' => 'ใบผ่านทหาร', 'work_certificate_copy' => 'สำเนาใบผ่านงาน',
            'driving_license' => 'สำเนาใบขับขี่', 'other' => 'เอกสารอื่นๆ',
        ];
        $yesNo = fn ($v) => is_null($v) ? '-' : ($v ? 'ใช่ / Yes' : 'ไม่ใช่ / No');
        $fmtDate = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : '-';
        $val = fn ($v) => filled($v) ? $v : '-';

        $documentsByType = $applicant->documents->keyBy('document_type');
        $photoDoc = $documentsByType->get('photo');
    @endphp

    <div class="header">
        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="company-name">GRAND SK</div>
                    <div class="doc-title">ใบสมัครงาน / Job Application Form</div>
                    <div class="doc-sub">ตำแหน่งที่สมัคร / Position applied for: <strong>{{ $applicant->position->title ?? '-' }}</strong></div>
                </td>
                <td style="width: 25%;" class="app-ref">
                    <div>เลขที่ใบสมัคร / Reference No.</div>
                    <div class="ref-no">APP-{{ str_pad($applicant->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div>วันที่ยื่นใบสมัคร: {{ $fmtDate($applicant->created_at) }}</div>
                </td>
                <td style="width: 15%; text-align: right;">
                    <div class="photo-box">
                        @if ($photoDoc && \Illuminate\Support\Facades\Storage::disk('public')->exists($photoDoc->file_path))
                            <img src="{{ str_replace('\\', '/', \Illuminate\Support\Facades\Storage::disk('public')->path($photoDoc->file_path)) }}">
                        @else
                            รูปถ่าย<br>1 นิ้ว
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ประวัติส่วนตัว --}}
    <div class="section-title">1. ประวัติส่วนตัว <span class="en">/ Personal Background</span></div>
    <table class="fields">
        <tr>
            <td class="label">ชื่อ-นามสกุล (TH)</td><td class="value">{{ $val($applicant->title_th.' '.$applicant->first_name_th.' '.$applicant->last_name_th) }}</td>
            <td class="label">Name (ENG)</td><td class="value">{{ $val($applicant->title_en.' '.$applicant->first_name_en.' '.$applicant->last_name_en) }}</td>
        </tr>
        <tr>
            <td class="label">ชื่อเล่น (TH) / Nickname (ENG)</td><td class="value">{{ $val($applicant->nickname_th) }} / {{ $val($applicant->nickname_en) }}</td>
            <td class="label">เงินเดือนที่ต้องการ</td><td class="value">{{ $applicant->expected_salary ? number_format($applicant->expected_salary, 2).' บาท' : '-' }}</td>
        </tr>
        <tr>
            <td class="label">วันเดือนปีเกิด / อายุ</td><td class="value">{{ $fmtDate($applicant->date_of_birth) }} ({{ $applicant->date_of_birth ? \Illuminate\Support\Carbon::parse($applicant->date_of_birth)->age : '-' }} ปี)</td>
            <td class="label">เพศ</td><td class="value">{{ $genderLabels[$applicant->gender] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">สัญชาติ / เชื้อชาติ</td><td class="value">{{ $val($applicant->nationality) }} / {{ $val($applicant->ethnicity) }}</td>
            <td class="label">ศาสนา</td><td class="value">{{ $val($applicant->religion) }}</td>
        </tr>
        <tr>
            <td class="label">ส่วนสูง / น้ำหนัก</td><td class="value">{{ $val($applicant->height_cm) }} ซม. / {{ $val($applicant->weight_kg) }} กก.</td>
            <td class="label">ประเทศ / ภูมิลำเนา</td><td class="value">{{ $val($applicant->country) }} / {{ $val($applicant->hometown) }}</td>
        </tr>
        <tr>
            <td class="label">มีโรคประจำตัว</td><td class="value">{{ $yesNo($applicant->has_congenital_disease) }} {{ $applicant->congenital_disease_detail ? '('.$applicant->congenital_disease_detail.')' : '' }}</td>
            <td class="label">มีประวัติอาชญากรรม</td><td class="value">{{ $yesNo($applicant->has_criminal_record) }} {{ $applicant->criminal_record_detail ? '('.$applicant->criminal_record_detail.')' : '' }}</td>
        </tr>
        <tr>
            <td class="label">เบอร์โทรศัพท์ / Email</td><td class="value">{{ $val($applicant->phone) }} / {{ $val($applicant->email) }}</td>
            <td class="label">Line ID</td><td class="value">{{ $val($applicant->line_id) }}</td>
        </tr>
        <tr>
            <td class="label">เลขบัตรประชาชน</td><td class="value">{{ $val($applicant->national_id_number) }}</td>
            <td class="label">วันออกบัตร / วันหมดอายุ</td><td class="value">{{ $fmtDate($applicant->id_issue_date) }} / {{ $fmtDate($applicant->id_expiry_date) }}</td>
        </tr>
        <tr>
            <td class="label">ที่อยู่ปัจจุบัน</td><td class="value" colspan="3">{{ $val($applicant->current_address) }}</td>
        </tr>
        <tr>
            <td class="label">สถานะความเป็นอยู่</td><td class="value">{{ $livingStatusLabels[$applicant->living_status] ?? '-' }}</td>
            <td class="label">สถานะครอบครัว</td><td class="value">{{ $maritalStatusLabels[$applicant->marital_status] ?? '-' }}</td>
        </tr>
        @if ($applicant->marital_status === 'married')
        <tr>
            <td class="label">ชื่อคู่สมรส / อาชีพ</td><td class="value">{{ $val($applicant->spouse_name) }} / {{ $val($applicant->spouse_occupation) }}</td>
            <td class="label">จำนวนบุตร</td><td class="value">{{ $val($applicant->children_count) }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">ชื่อบิดา / อาชีพ</td><td class="value">{{ $val($applicant->father_name) }} / {{ $val($applicant->father_occupation) }}</td>
            <td class="label">ชื่อมารดา / อาชีพ</td><td class="value">{{ $val($applicant->mother_name) }} / {{ $val($applicant->mother_occupation) }}</td>
        </tr>
        <tr>
            <td class="label">สถานะภาพทางการทหาร</td><td class="value">{{ $militaryStatusLabels[$applicant->military_status] ?? '-' }}</td>
            <td class="label">ประวัติหมิ่นพระบรมเดชานุภาพ</td><td class="value">{{ $yesNo($applicant->has_lese_majeste_record) }}</td>
        </tr>
    </table>

    {{-- ประวัติการศึกษา --}}
    <div class="section-title">2. ประวัติการศึกษา <span class="en">/ Educational Background</span></div>
    @forelse ($applicant->education as $edu)
        <div class="sub-block">
            <div class="sub-block-title">{{ $degreeLevelLabels[$edu->degree_level] ?? $edu->degree_level }}</div>
            <table class="fields">
                <tr>
                    <td class="label">ชื่อสถาบัน</td><td class="value">{{ $val($edu->institution_name) }}</td>
                    <td class="label">จังหวัด/ประเทศ</td><td class="value">{{ $val($edu->province_country) }}</td>
                </tr>
                <tr>
                    <td class="label">ปีที่ศึกษา (พ.ศ.)</td><td class="value">{{ $val($edu->start_year) }} - {{ $val($edu->end_year) }}</td>
                    <td class="label">วิชา/วุฒิที่ได้รับ / GPA</td><td class="value">{{ $val($edu->major) }} / {{ $val($edu->gpa) }}</td>
                </tr>
            </table>
        </div>
    @empty
        <p style="color:#a8a29e; font-size:10px;">ไม่ได้ระบุข้อมูลการศึกษา</p>
    @endforelse

    {{-- ความสามารถด้านภาษา --}}
    <div class="section-title">3. ความสามารถด้านภาษา <span class="en">/ Languages</span></div>
    <table class="docs">
        <tr style="font-weight:bold; background:#fafaf9;">
            <td>ภาษา</td><td>การพูด</td><td>การเข้าใจ</td><td>การอ่าน</td><td>การเขียน</td>
        </tr>
        @forelse ($applicant->languages as $lang)
        <tr>
            <td>{{ $lang->language === 'other' ? $lang->custom_language_name : ($languageLabels[$lang->language] ?? $lang->language) }}</td>
            <td>{{ $lang->speaking_level }}</td>
            <td>{{ $lang->understanding_level }}</td>
            <td>{{ $lang->reading_level }}</td>
            <td>{{ $lang->writing_level }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="color:#a8a29e;">ไม่ได้ระบุความสามารถด้านภาษา</td></tr>
        @endforelse
    </table>
    @if ($applicant->language_certificate_note)
        <p style="font-size:10px; margin-top:4px;">ใบรับรองด้านภาษา: {{ $applicant->language_certificate_note }}</p>
    @endif

    {{-- ประวัติการทำงาน --}}
    <div class="section-title">4. ประวัติการทำงาน <span class="en">/ Employment History</span></div>
    @forelse ($applicant->workHistories as $work)
        <div class="sub-block">
            <div class="sub-block-title">สถานประกอบที่ {{ $work->order_no }}: {{ $work->company_name }}</div>
            <table class="fields">
                <tr>
                    <td class="label">ตำแหน่ง</td><td class="value">{{ $val($work->position) }}</td>
                    <td class="label">ระยะเวลา (พ.ศ.)</td><td class="value">{{ $val($work->start_year) }} - {{ $val($work->end_year) }}</td>
                </tr>
                <tr>
                    <td class="label">หัวหน้างาน / เบอร์โทร</td><td class="value">{{ $val($work->supervisor_name) }} / {{ $val($work->supervisor_phone) }}</td>
                    <td class="label">เงินเดือนล่าสุด</td><td class="value">{{ $work->last_salary ? number_format($work->last_salary, 2).' บาท' : '-' }}</td>
                </tr>
                @if ($work->resignation_reason)
                <tr>
                    <td class="label">สาเหตุที่ออก</td><td class="value" colspan="3">{{ $work->resignation_reason }}</td>
                </tr>
                @endif
            </table>
        </div>
    @empty
        <p style="color:#a8a29e; font-size:10px;">ไม่ได้ระบุประวัติการทำงาน</p>
    @endforelse

    {{-- ข้อมูลอื่นๆ --}}
    <div class="section-title">5. ข้อมูลอื่นๆ <span class="en">/ Other information</span></div>
    @foreach ($applicant->references as $ref)
        <div class="sub-block">
            <div class="sub-block-title">บุคคลอ้างอิงคนที่ {{ $ref->order_no }}</div>
            <table class="fields">
                <tr>
                    <td class="label">ชื่อ-นามสกุล / ความสัมพันธ์</td><td class="value">{{ $val($ref->name) }} / {{ $val($ref->relationship) }}</td>
                    <td class="label">เบอร์โทร</td><td class="value">{{ $val($ref->phone) }}</td>
                </tr>
                <tr>
                    <td class="label">สถานที่ทำงาน-ที่อยู่</td><td class="value" colspan="3">{{ $val($ref->workplace_address) }}</td>
                </tr>
            </table>
        </div>
    @endforeach
    <table class="fields">
        <tr>
            <td class="label">ความรู้ความสามารถพิเศษ</td><td class="value" colspan="3">{{ $val($applicant->special_skills) }}</td>
        </tr>
        <tr>
            <td class="label">สามารถหาผู้ค้ำประกันได้</td><td class="value">{{ $yesNo($applicant->guarantor_required_ok) }}</td>
            <td class="label">ทำงานเป็นกะหมุนเวียนได้</td><td class="value">{{ $yesNo($applicant->shift_work_ok) }}</td>
        </tr>
        <tr>
            <td class="label">หนี้นอกระบบ / ดอกเบี้ย</td><td class="value">{{ $val($applicant->informal_debt_amount) }} บาท / {{ $val($applicant->informal_debt_interest_rate) }}%</td>
            <td class="label">วงเงินบัตรเครดิต / ยอดค้าง</td><td class="value">{{ $val($applicant->credit_card_debt_amount) }} / {{ $val($applicant->total_outstanding_balance) }}</td>
        </tr>
        <tr>
            <td class="label">ประวัติเจ็บป่วย/อุบัติเหตุ (3 ปี)</td><td class="value" colspan="3">{{ $val($applicant->illness_or_accident_history) }}</td>
        </tr>
        <tr>
            <td class="label">บุคคลในบริษัทที่รู้จัก / ความสัมพันธ์</td><td class="value">{{ $val($applicant->known_person_in_company) }} / {{ $val($applicant->known_person_relation) }}</td>
            <td class="label">วันที่พร้อมเริ่มงาน</td><td class="value">{{ $fmtDate($applicant->available_start_date) }}</td>
        </tr>
        <tr>
            <td class="label">ช่องทางที่ทราบข่าว</td><td class="value" colspan="3">
                {{ $val(implode(', ', (array) $applicant->source_channels)) }}
                {{ $applicant->source_channel_other ? ' ('.$applicant->source_channel_other.')' : '' }}
            </td>
        </tr>
        @if ($applicant->referred_friend_name)
        <tr>
            <td class="label">ชื่อเพื่อน/ญาติที่แนะนำ</td><td class="value" colspan="3">{{ $applicant->referred_friend_name }}</td>
        </tr>
        @endif
    </table>

    {{-- เอกสารแนบ --}}
    <div class="section-title">6. เอกสารแนบ <span class="en">/ Attachments</span></div>
    <table class="docs">
        @foreach ($documentTypeLabels as $type => $label)
            <tr>
                <td style="width: 70%;">{{ $label }}</td>
                <td style="width: 30%;">
                    @if ($documentsByType->has($type))
                        <span class="doc-yes">แนบแล้ว</span>
                    @else
                        <span class="doc-no">— ไม่ได้แนบ</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    {{-- การยินยอมและลงชื่อ --}}
    <div class="section-title">7. การยินยอมและลงชื่อ <span class="en">/ Consent &amp; Signature</span></div>
    <div class="consent-box">
        <p>
            <span class="{{ $applicant->data_accuracy_consent ? 'checked' : '' }}">[{{ $applicant->data_accuracy_consent ? 'ยอมรับแล้ว' : 'ยังไม่ยอมรับ' }}]</span>
            ข้าพเจ้าขอรับรองว่าข้อความข้างต้นทั้งหมดนี้เป็นความจริงทุกประการ และยินยอมให้เก็บ ใช้ เปิดเผย ตรวจสอบ ข้อมูลดังกล่าวได้ตลอดเวลาที่จำเป็นเพื่อการสมัครงาน และการเป็นพนักงานของบริษัทได้
        </p>
        <p>
            <span class="{{ $applicant->privacy_policy_consent ? 'checked' : '' }}">[{{ $applicant->privacy_policy_consent ? 'ยอมรับแล้ว' : 'ยังไม่ยอมรับ' }}]</span>
            ข้าพเจ้าได้อ่านนโยบายการคุ้มครองข้อมูลส่วนบุคคลสำหรับบุคลากรของบริษัท และยินยอมให้เก็บ ใช้ เปิดเผย ตรวจสอบ ข้อมูลดังกล่าวได้ตลอดระยะเวลาการเป็นพนักงานของบริษัท
        </p>
    </div>

    <table class="signature-block">
        <tr>
            <td>
                <div class="signature-line">
                    {{ $applicant->signature_name ?: '..............................................' }}
                </div>
                ลงชื่อผู้สมัครงาน / Applicant's Signature<br>
                วันที่ {{ $fmtDate($applicant->signed_at) }}
            </td>
            <td>
                <div class="signature-line">&nbsp;</div>
                ลงชื่อเจ้าหน้าที่ HR / HR Officer<br>
                วันที่ ..............................................
            </td>
        </tr>
    </table>

    <div class="footer">
        เอกสารนี้สร้างโดยระบบอัตโนมัติจากข้อมูลที่ผู้สมัครกรอกผ่านเว็บไซต์ Grand SK เมื่อ {{ now()->format('d/m/Y H:i') }} น.
    </div>

</body>
</html>
