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
        body { font-family: 'Sarabun', sans-serif; font-size: 9.5px; color: #1c1917; }

        .center { text-align: center; }
        h1.doc-title { text-align: center; font-size: 15px; margin: 0 0 2px 0; }
        p.doc-sub { text-align: center; font-size: 10px; margin: 0 0 8px 0; color: #57534e; }

        table.outer { width: 100%; border-collapse: collapse; }
        table.outer td, table.outer th { border: 1px solid #44403c; padding: 3px 5px; vertical-align: top; }

        .logos-row td { text-align: center; padding: 6px; }
        .logos-row img { height: 22px; }

        .section-bar {
            background: #78350f; color: #ffffff; font-weight: bold; font-size: 11px;
            padding: 4px 8px; margin-top: 8px;
        }
        .section-bar .en { font-weight: normal; font-size: 9px; color: #fde68a; }

        table.fields { width: 100%; border-collapse: collapse; }
        table.fields td { border: 1px solid #44403c; padding: 3px 5px; vertical-align: top; }
        .f-label { font-size: 8px; color: #57534e; }
        .f-value { font-size: 10px; font-weight: bold; min-height: 11px; }
        .radio { display: inline-block; }
        .radio .mark { font-weight: bold; }

        table.grid-table { width: 100%; border-collapse: collapse; }
        table.grid-table th { background: #fef3c7; color: #78350f; border: 1px solid #44403c; padding: 3px 4px; font-size: 8px; text-align: center; }
        table.grid-table td { border: 1px solid #44403c; padding: 3px 4px; font-size: 9px; text-align: center; }
        table.grid-table td.left { text-align: left; }

        .doc-title-2 { font-weight: bold; font-size: 11px; color: #78350f; margin-top: 10px; margin-bottom: 3px; }
        .note-box { border: 1px solid #44403c; padding: 5px 8px; font-size: 9px; margin-top: 4px; }
        .decl { font-size: 9px; line-height: 1.5; margin: 4px 0; }
        .sig-table { width: 100%; margin-top: 14px; }
        .sig-table td { width: 50%; text-align: center; font-size: 9px; padding-top: 22px; }
        .sig-line { border-top: 1px solid #57534e; padding-top: 3px; width: 75%; margin: 0 auto; }

        .footer-note { margin-top: 10px; font-size: 8px; color: #a8a29e; text-align: center; }
    </style>
</head>
<body>

    @php
        $genderLabels = ['male' => 'ชาย', 'female' => 'หญิง', 'other' => 'ไม่ระบุ'];
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
            'diploma_high_vocational' => 'อนุปริญญา/ปวส.', 'vocational' => 'อาชีวะศึกษา',
            'bachelor' => 'ปริญญาตรี', 'master' => 'ปริญญาโท', 'other' => 'อื่นๆ',
        ];
        $languageLabels = ['thai' => 'ไทย / Thai', 'english' => 'อังกฤษ / English', 'chinese' => 'จีน / Chinese', 'other' => 'อื่น ๆ / Other'];
        $documentTypeLabels = [
            'photo' => 'รูปถ่ายหน้าตรงขนาด 1" จำนวน 1 รูป', 'house_registration' => 'สำเนาทะเบียนบ้าน',
            'id_card_copy' => 'สำเนาบัตรประชาชน', 'education_certificate' => 'ใบรับรองการศึกษา',
            'military_certificate' => 'ใบผ่านทหาร', 'driving_license' => 'ใบขับขี่ (เฉพาะตำแหน่งที่ร้องขอ)',
            'work_certificate_copy' => 'สำเนาใบผ่านงาน',
        ];
        $val = fn ($v) => filled($v) ? $v : '';
        $fmtDate = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : '';
        $radio = fn ($active) => $active ? '[X]' : '[&nbsp;&nbsp;]'; // avoid unicode circle glyphs the Thai font may not have
        $documentsByType = $applicant->documents->keyBy('document_type');
        $photoDoc = $documentsByType->get('photo');

        $tier = function ($v) {
            if (is_null($v)) {
                return null;
            }

            return $v >= 4 ? 'exc' : ($v >= 2 ? 'good' : 'fair');
        };
    @endphp

    <h1 class="doc-title">ใบสมัครงาน</h1>
    <p class="doc-sub">(Application Form)</p>

    <table class="outer logos-row">
        <tr>
            <td style="width:25%;"><img src="{{ public_path('images/logos/grand-sk.png') }}"></td>
            <td style="width:25%;"><img src="{{ public_path('images/logos/sk-polymer.png') }}"></td>
            <td style="width:25%;"><img src="{{ public_path('images/logos/trt.png') }}"></td>
            <td style="width:25%;"><img src="{{ public_path('images/logos/polymate.png') }}"></td>
        </tr>
    </table>

    <table class="outer" style="margin-top:-1px;">
        <tr>
            <td style="width:25%;">
                <div class="f-label">วันที่สมัคร / Date</div>
                <div class="f-value">{{ $fmtDate($applicant->created_at) }}</div>
            </td>
            <td style="width:37.5%;">
                <div class="f-label">ตำแหน่งที่ต้องการ / Position applied for</div>
                <div class="f-value">{{ $applicant->position->title ?? '' }}</div>
            </td>
            <td style="width:37.5%;">
                <div class="f-label">เงินเดือนที่ต้องการ / Expected Starting salary</div>
                <div class="f-value">{{ $applicant->expected_salary ? number_format($applicant->expected_salary, 2).' บาท' : '' }}</div>
            </td>
        </tr>
    </table>

    <table class="outer" style="margin-top:-1px;">
        <tr>
            <td style="width:75%;">
                <div class="f-label" style="font-weight:bold; margin-bottom:3px;">หลักฐานประกอบการสมัครงาน / Supporting documents</div>
                <table style="width:100%; border-collapse:collapse;">
                    @foreach ($documentTypeLabels as $type => $label)
                        <tr>
                            <td style="border:none; padding:1px 2px; width:16px;">{!! $radio($documentsByType->has($type)) !!}</td>
                            <td style="border:none; padding:1px 2px; font-size:9px;">{{ $label }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
            <td style="width:25%; text-align:center; vertical-align:middle;">
                <div class="f-label">รูปถ่าย / PHOTO</div>
                @if ($photoDoc && \Illuminate\Support\Facades\Storage::disk('local')->exists($photoDoc->file_path))
                    <img src="{{ str_replace('\\', '/', \Illuminate\Support\Facades\Storage::disk('local')->path($photoDoc->file_path)) }}" style="width:70px; height:88px; margin-top:4px;">
                @else
                    <div style="width:70px; height:88px; border:1px dashed #a8a29e; margin:4px auto 0;"></div>
                @endif
            </td>
        </tr>
    </table>

    <p style="text-align:center; font-size:9px; margin:6px 0;">
        ใบสมัครเป็นส่วนหนึ่งในการพิจารณา โปรดกรอกข้อความให้ครบถ้วน ข้าพเจ้าผู้สมัครงาน ขอให้ข้อมูลส่วนบุคคลของข้าพเจ้าตามความเป็นจริงดังต่อไปนี้<br>
        <em>Application Form is a part of consideration, Please fill this form completely.</em>
    </p>

    {{-- ================= ประวัติส่วนตัว ================= --}}
    <div class="section-bar">ประวัติส่วนตัว <span class="en">/ Personal Background</span></div>
    <table class="fields">
                    <tr>
                        <td style="width:75%;">
                            <div class="f-label">ชื่อ-สกุลภาษาไทย / Name (TH)</div>
                            <div class="f-value">{{ $applicant->title_th }} {{ $applicant->first_name_th }} {{ $applicant->last_name_th }}</div>
                        </td>
                        <td style="width:25%;">
                            <div class="f-label">เพศ / Sex</div>
                            <div class="f-value">
                                {!! $radio($applicant->gender === 'male') !!} ชาย
                                &nbsp; {!! $radio($applicant->gender === 'female') !!} หญิง
                                &nbsp; {!! $radio($applicant->gender === 'other') !!} ไม่ระบุ
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="f-label">ชื่อ-สกุลภาษาอังกฤษ / Name in English</div>
                            <div class="f-value">{{ $applicant->title_en }} {{ $applicant->first_name_en }} {{ $applicant->last_name_en }} ({{ $applicant->nickname_en }})</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:25%;"><div class="f-label">วันเดือนปีเกิด / Date of Birth</div><div class="f-value">{{ $fmtDate($applicant->date_of_birth) }}</div></td>
                        <td style="width:25%;"><div class="f-label">สัญชาติ / Nationality</div><div class="f-value">{{ $applicant->nationality }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="f-label">เชื้อชาติ / Race</div><div class="f-value">{{ $applicant->ethnicity }}</div></td>
                        <td><div class="f-label">ศาสนา / Religion</div><div class="f-value">{{ $applicant->religion }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="f-label">อายุ / Age</div><div class="f-value">{{ $applicant->date_of_birth ? \Illuminate\Support\Carbon::parse($applicant->date_of_birth)->age.' ปี' : '' }}</div></td>
                        <td><div class="f-label">ส่วนสูง / Height &nbsp;&nbsp; น้ำหนัก / Weight</div><div class="f-value">{{ $applicant->height_cm }} ซม. &nbsp; {{ $applicant->weight_kg }} กก.</div></td>
                    </tr>
                    <tr>
                        <td><div class="f-label">ภูมิลำเนา / Hometown</div><div class="f-value">{{ $applicant->hometown }}</div></td>
                        <td><div class="f-label">โทรศัพท์ที่ติดต่อ / Telephone</div><div class="f-value">{{ $applicant->phone }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="f-label">Line ID / Facebook ID</div><div class="f-value">{{ $applicant->line_id }} / {{ $applicant->facebook }}</div></td>
                        <td><div class="f-label">E-mail</div><div class="f-value">{{ $applicant->email }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="f-label">Instagram / Twitter</div><div class="f-value">{{ $applicant->instagram }} / {{ $applicant->twitter }}</div></td>
                        <td>
                            <div class="f-label">ประวัติหมิ่นพระมหากษัตริย์ / Anti monarch record</div>
                            <div class="f-value">{!! $radio($applicant->has_lese_majeste_record) !!} มี &nbsp; {!! $radio(! $applicant->has_lese_majeste_record) !!} ไม่มี</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><div class="f-label">ที่อยู่ปัจจุบันที่ติดต่อได้สะดวก / Present Address</div><div class="f-value">{{ $applicant->current_address }}</div></td>
                    </tr>
                    <tr>
                        <td><div class="f-label">บัตรประชาชนเลขที่ / I.D. Card No.</div><div class="f-value">{{ $applicant->national_id_number }}</div></td>
                        <td><div class="f-label">วันออกบัตร / บัตรหมดอายุ</div><div class="f-value">{{ $fmtDate($applicant->id_issue_date) }} - {{ $fmtDate($applicant->id_expiry_date) }}</div></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="f-label">สถานะความเป็นอยู่ / Living Status</div>
                            <div class="f-value">
                                {!! $radio($applicant->living_status === 'own_home') !!} บ้านส่วนตัว
                                &nbsp; {!! $radio($applicant->living_status === 'rent_home') !!} บ้านเช่า
                                &nbsp; {!! $radio($applicant->living_status === 'with_parents') !!} อาศัยบิดามารดา
                                &nbsp; {!! $radio($applicant->living_status === 'with_other') !!} อาศัยอยู่กับผู้อื่น
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="f-label">สถานะครอบครัว / Marital Status</div>
                            <div class="f-value">
                                {!! $radio($applicant->marital_status === 'single') !!} โสด
                                &nbsp; {!! $radio($applicant->marital_status === 'married') !!} แต่งงาน
                                &nbsp; {!! $radio($applicant->marital_status === 'divorced') !!} หย่า
                                &nbsp; {!! $radio($applicant->marital_status === 'widowed') !!} หม้าย
                                &nbsp; {!! $radio($applicant->marital_status === 'separated') !!} แยกกันอยู่
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="f-label">กรณีแต่งงาน / If Married</div>
                            <div class="f-value">
                                {!! $radio($applicant->marriage_registered === true) !!} จดทะเบียน
                                &nbsp; {!! $radio($applicant->marriage_registered === false) !!} ไม่ได้จด
                            </div>
                        </td>
                        <td><div class="f-label">ชื่อคู่สมรส / จำนวนบุตร</div><div class="f-value">{{ $applicant->spouse_name }} &nbsp; ({{ $applicant->children_count ?? 0 }} คน)</div></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="f-label">ชื่อบิดา / Name of Father</div>
                            <div class="f-value">{{ $applicant->father_name }} {{ $applicant->father_age ? '('.$applicant->father_age.' ปี)' : '' }} {{ $applicant->father_occupation }}
                                &nbsp; {!! $radio($applicant->father_alive === true) !!} มีชีวิต {!! $radio($applicant->father_alive === false) !!} ถึงแก่กรรม</div>
                        </td>
                        <td>
                            <div class="f-label">ชื่อมารดา / Name of Mother</div>
                            <div class="f-value">{{ $applicant->mother_name }} {{ $applicant->mother_age ? '('.$applicant->mother_age.' ปี)' : '' }} {{ $applicant->mother_occupation }}
                                &nbsp; {!! $radio($applicant->mother_alive === true) !!} มีชีวิต {!! $radio($applicant->mother_alive === false) !!} ถึงแก่กรรม</div>
                        </td>
                    </tr>
                    <tr>
                        <td><div class="f-label">จำนวนพี่น้อง (รวมท่านด้วย) / ท่านเป็นคนที่</div><div class="f-value">{{ $applicant->siblings_count }} คน / คนที่ {{ $applicant->birth_order }}</div></td>
                        <td><div class="f-label">สถานะทางทหาร / Military Service</div><div class="f-value">{{ $militaryStatusLabels[$applicant->military_status] ?? '' }}</div></td>
                    </tr>
                </table>

    {{-- ================= ประวัติการศึกษา ================= --}}
    <div class="section-bar">ประวัติการศึกษา <span class="en">/ Educational Background</span></div>
    <table class="grid-table">
                    <tr>
                        <th style="width:16%;">ระดับการศึกษา<br>Education</th>
                        <th style="width:24%;">ชื่อสถาบัน<br>Name of Institute</th>
                        <th style="width:14%;">จังหวัด-ประเทศ<br>Country</th>
                        <th style="width:14%;">ปีการศึกษา<br>Year Attended</th>
                        <th style="width:24%;">วิชาที่ศึกษา/วุฒิที่ได้รับ<br>Course Taken/Completed</th>
                        <th style="width:8%;">เกรด<br>GPA</th>
                    </tr>
                    @forelse ($applicant->education as $edu)
                        <tr>
                            <td class="left">{{ $degreeLevelLabels[$edu->degree_level] ?? $edu->degree_level }}</td>
                            <td class="left">{{ $edu->institution_name }}</td>
                            <td>{{ $edu->province_country }}</td>
                            <td>{{ $edu->start_year }} - {{ $edu->end_year }}</td>
                            <td class="left">{{ $edu->major }}</td>
                            <td>{{ $edu->gpa }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="left" style="color:#a8a29e;">ไม่ได้ระบุข้อมูลการศึกษา</td></tr>
                    @endforelse
                </table>

    {{-- ================= ภาษา ================= --}}
    <div class="section-bar">ภาษา <span class="en">/ Languages</span></div>
    <table class="grid-table">
                    <tr>
                        <th rowspan="2" style="width:12%;">ประเภทภาษา<br>Type</th>
                        <th colspan="3">การพูด Speaking</th>
                        <th colspan="3">การเข้าใจ Understanding</th>
                        <th colspan="3">การอ่าน Reading</th>
                        <th colspan="3">การเขียน Writing</th>
                    </tr>
                    <tr>
                        @for ($i = 0; $i < 4; $i++)
                            <th style="width:7.3%;">ดีมาก</th><th style="width:7.3%;">ดี</th><th style="width:7.3%;">พอใช้</th>
                        @endfor
                    </tr>
                    @php $langBy = $applicant->languages->keyBy('language'); @endphp
                    @foreach (['thai', 'english', 'chinese', 'other'] as $langKey)
                        @php $lang = $langBy->get($langKey); @endphp
                        <tr>
                            <td class="left">{{ $langKey === 'other' && $lang ? $lang->custom_language_name : $languageLabels[$langKey] }}</td>
                            @foreach (['speaking_level', 'understanding_level', 'reading_level', 'writing_level'] as $skill)
                                @php $t = $lang ? $tier($lang->$skill) : null; @endphp
                                <td>{!! $radio($t === 'exc') !!}</td><td>{!! $radio($t === 'good') !!}</td><td>{!! $radio($t === 'fair') !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="13" class="left">ใบรับรองด้านภาษา / Certificate of Language: &nbsp; {{ $applicant->language_certificate_note }}</td>
                    </tr>
                </table>

    {{-- ================= ประวัติการทำงาน ================= --}}
    <div class="section-bar">ประวัติการทำงาน <span class="en">/ Employment History</span></div>
    <table class="grid-table">
                    <tr>
                        <th style="width:20%;">ชื่อสถานประกอบการ<br>List of Employed</th>
                        <th style="width:14%;">ระยะงาน<br>Date Employed</th>
                        <th style="width:12%;">ตำแหน่ง<br>Position</th>
                        <th style="width:14%;">หัวหน้างาน / เบอร์โทร<br>Leader / Phone</th>
                        <th style="width:12%;">เงินเดือน<br>Last Salary</th>
                        <th style="width:28%;">สาเหตุที่ออก<br>Reasons of resignation</th>
                    </tr>
                    @forelse ($applicant->workHistories as $work)
                        <tr>
                            <td class="left">{{ $work->company_name }}</td>
                            <td>{{ $work->start_year }} - {{ $work->end_year }}</td>
                            <td class="left">{{ $work->position }}</td>
                            <td class="left">{{ $work->supervisor_name }} {{ $work->supervisor_phone ? '/ '.$work->supervisor_phone : '' }}</td>
                            <td>{{ $work->last_salary ? number_format($work->last_salary, 0) : '' }}</td>
                            <td class="left">{{ $work->resignation_reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="left" style="color:#a8a29e;">ไม่ได้ระบุประวัติการทำงาน</td></tr>
                    @endforelse
                </table>

    <p style="text-align:center; font-size:9px; margin:8px 0 3px;">
        บุคคลที่ไม่ใช่ญาติซึ่งทราบประวัติของท่านและบริษัทฯ สามารถสอบถามได้<br>
        <em>Persons other than relatives can be contacted</em>
    </p>
    <table class="grid-table">
        <tr>
            <th style="width:22%;">ชื่อ-นามสกุล<br>Name</th>
            <th style="width:18%;">ความสัมพันธ์<br>Relationship</th>
            <th style="width:26%;">สถานที่ทำงาน/ที่อยู่<br>Firm Address</th>
            <th style="width:16%;">ตำแหน่ง<br>Position</th>
            <th style="width:18%;">โทรศัพท์<br>Telephone</th>
        </tr>
        @forelse ($applicant->references as $ref)
            <tr>
                <td class="left">{{ $ref->name }}</td>
                <td class="left">{{ $ref->relationship }}</td>
                <td class="left">{{ $ref->workplace_address }}</td>
                <td class="left">{{ $ref->position }}</td>
                <td>{{ $ref->phone }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="left" style="color:#a8a29e;">ไม่ได้ระบุบุคคลอ้างอิง</td></tr>
        @endforelse
    </table>

    <div style="page-break-inside: avoid;">
        <table class="fields" style="margin-top:6px;">
            <tr>
                <td colspan="2"><div class="f-label">ความรู้พิเศษ/ความสามารถพิเศษ / Special ability</div><div class="f-value">{{ $applicant->special_skills }}</div></td>
            </tr>
            <tr>
                <td style="width:50%;">
                    <div class="f-label">ในตำแหน่งงานที่ต้องมีการค้ำประกัน / Can you provide a guarantor</div>
                    <div class="f-value">{!! $radio($applicant->guarantor_required_ok === true) !!} ไม่ขัดข้อง (Yes) &nbsp; {!! $radio($applicant->guarantor_required_ok === false) !!} ขัดข้อง (No)</div>
                </td>
                <td>
                    <div class="f-label">ในการปฏิบัติงานเป็นกะหมุนเวียนกันไป / Can you work shift by shift</div>
                    <div class="f-value">{!! $radio($applicant->shift_work_ok === true) !!} ไม่ขัดข้อง (Yes) &nbsp; {!! $radio($applicant->shift_work_ok === false) !!} ขัดข้อง (No)</div>
                </td>
            </tr>
            <tr>
                <td><div class="f-label">บุคคลในบริษัทฯ ที่ท่านรู้จักคุ้นเคยชื่อ / ความสัมพันธ์</div><div class="f-value">{{ $applicant->known_person_in_company }} / {{ $applicant->known_person_relation }}</div></td>
                <td><div class="f-label">ท่านพร้อมที่จะปฏิบัติงานกับบริษัทฯ ได้ในวันที่</div><div class="f-value">{{ $fmtDate($applicant->available_start_date) }}</div></td>
            </tr>
            <tr>
                <td colspan="2"><div class="f-label">ท่านทราบข่าวการรับสมัครงานจาก / Where do you know recruitment from</div>
                    <div class="f-value">{{ implode(', ', (array) $applicant->source_channels) }} {{ $applicant->source_channel_other ? '('.$applicant->source_channel_other.')' : '' }} {{ $applicant->referred_friend_name ? '— แนะนำโดย '.$applicant->referred_friend_name : '' }}</div>
                </td>
            </tr>
        </table>

        <div class="note-box">
            <div style="font-size:9px;">เพื่อเป็นประโยชน์สำหรับท่าน บริษัทมีโครงการช่วยเหลือและวางแผนด้านการเงินสำหรับพนักงาน ขอให้ท่านกรอกข้อมูลดังต่อไปนี้</div>
            <div class="f-value" style="margin-top:3px;">
                ชำระหนี้นอกระบบจำนวน {{ $applicant->informal_debt_amount ?? '-' }} บาทต่อเดือน &nbsp; ดอกเบี้ยร้อยละ {{ $applicant->informal_debt_interest_rate ?? '-' }} บาทต่อเดือน
            </div>
            <div class="f-value">
                มีบัตรเครดิต/บัตรกดเงินสด และมียอดค้างชำระเป็นจำนวนเงินรวม {{ $applicant->credit_card_debt_amount ?? '-' }} / {{ $applicant->total_outstanding_balance ?? '-' }} บาท
            </div>
            <div class="f-value">
                ในระยะเวลา 3 ปีที่ผ่านมาท่านเจ็บป่วยถึงขั้นนอนโรงพยาบาล หรือเคยประสบอุบัติเหตุ หรือไม่: {{ $applicant->illness_or_accident_history ?: '-' }}
            </div>
        </div>
    </div>

    {{-- ================= วันและเวลาปฏิบัติงาน ================= --}}
    <div class="doc-title-2">วันและเวลาปฏิบัติงาน / Working days and hours</div>
    <div class="note-box" style="font-size:8.5px; line-height:1.5;">
        <strong>กลุ่มพนักงานที่ไม่เกี่ยวข้องกับการผลิต / Employee group that is not related to the production</strong><br>
        - สัปดาห์ที่ 1, 3 ปฏิบัติงานวันจันทร์ถึงวันศุกร์ และวันเสาร์ทำงานรูปแบบ Work From Anywhere ครึ่งวันเช้า<br>
        - สัปดาห์ที่ 2, 4 ปฏิบัติงานวันจันทร์ถึงวันศุกร์ วันเสาร์เป็นวันหยุดประจำสัปดาห์<br>
        - สัปดาห์ที่ 5 ปฏิบัติงานวันเสาร์ (กิจกรรม CSR) ตามข้อบังคับการทำงานของแต่ละบริษัท<br><br>
        <strong>กลุ่มพนักงานที่เกี่ยวข้องกับการผลิต / Employee group that is related to the production</strong><br>
        - ปฏิบัติงานวันจันทร์ถึงวันเสาร์ ตามข้อบังคับการทำงานของแต่ละบริษัท (เลือกหยุดวันเสาร์ได้ 2 วันต่อเดือน)
    </div>

    <p class="decl">
        ข้าพเจ้าขอรับรองว่าข้อความข้างต้นทั้งหมดนี้เป็นความจริงทุกประการ หากข้อความตอนหนึ่งตอนใดไม่ตรงกับความเป็นจริง ข้าพเจ้าขอยอมรับว่าการว่าจ้างที่ตกลงนั้นเป็นอันโมฆะทันที
        และยินยอมให้เก็บ ใช้ เปิดเผย ตรวจสอบ ข้อมูลดังกล่าวได้ตลอดเวลาที่จำเป็นเพื่อการสมัครงาน และการเป็นพนักงานของบริษัทได้
        <span style="font-weight:bold; color: {{ $applicant->data_accuracy_consent ? '#15803d' : '#dc2626' }};">
            [{{ $applicant->data_accuracy_consent ? 'ยอมรับแล้ว' : 'ยังไม่ยอมรับ' }}]
        </span>
    </p>
    <p class="decl">
        ข้าพเจ้าได้อ่านนโยบายการคุ้มครองข้อมูลส่วนบุคคลสำหรับบุคลากรของบริษัท ข้าพเจ้ารับทราบและยินยอมให้เก็บ ใช้ เปิดเผย ตรวจสอบ
        ข้อมูลดังกล่าวได้ตลอดเวลาที่จำเป็นเพื่อการสมัครงาน และตลอดระยะเวลาการเป็นพนักงานของบริษัท
        <span style="font-weight:bold; color: {{ $applicant->privacy_policy_consent ? '#15803d' : '#dc2626' }};">
            [{{ $applicant->privacy_policy_consent ? 'ยอมรับแล้ว' : 'ยังไม่ยอมรับ' }}]
        </span>
    </p>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">วันที่ {{ $fmtDate($applicant->signed_at) }}</div>
            </td>
            <td>
                <div class="sig-line">{{ $applicant->signature_name ?: '..............................................' }}</div>
                ลงชื่อผู้สมัครงาน / Applicant's Signature
            </td>
        </tr>
    </table>

    <div class="doc-title-2" style="text-align:center;">สำหรับเจ้าหน้าที่ฝ่ายทรัพยากรบุคคล</div>
    <table class="fields">
        <tr>
            <td style="width:40%;">1. ตรวจสอบ Black List SKP</td>
            <td>{!! $radio($applicant->hr_blacklist_checked === true) !!} ผ่าน &nbsp; {!! $radio($applicant->hr_blacklist_checked === false) !!} ไม่ผ่าน</td>
        </tr>
        <tr>
            <td>2. ตรวจสอบประวัติการทำงาน</td>
            <td>{!! $radio($applicant->hr_criminal_checked === true) !!} ผ่าน &nbsp; {!! $radio($applicant->hr_criminal_checked === false) !!} ไม่ผ่าน</td>
        </tr>
        <tr>
            <td>3. ตรวจสอบทัศนคติ Social Network</td>
            <td>{!! $radio($applicant->hr_social_checked === true) !!} ผ่าน &nbsp; {!! $radio($applicant->hr_social_checked === false) !!} ไม่ผ่าน</td>
        </tr>
        <tr>
            <td>4. หมายเหตุ</td>
            <td>{{ $applicant->hr_verification_note }}</td>
        </tr>
    </table>

    <div class="footer-note">
        เลขที่ใบสมัคร APP-{{ str_pad($applicant->id, 6, '0', STR_PAD_LEFT) }} · เอกสารนี้สร้างโดยระบบอัตโนมัติจากข้อมูลที่ผู้สมัครกรอกผ่านเว็บไซต์ Grand SK เมื่อ {{ now()->format('d/m/Y H:i') }} น. · FS-A21-001
    </div>

</body>
</html>
