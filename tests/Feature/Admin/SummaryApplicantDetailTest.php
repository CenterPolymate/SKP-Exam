<?php

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantEducation;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantReference;
use App\Models\ApplicantWorkHistory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

function makeAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function makeFullApplicant(array $overrides = []): Applicant
{
    $position = Position::create(['title' => 'Test Position', 'is_active' => true]);

    $applicant = Applicant::create(array_merge([
        'position_id' => $position->id,
        'expected_salary' => 25000,
        'title_th' => 'นาย', 'first_name_th' => 'สมชาย', 'last_name_th' => 'ใจดี', 'nickname_th' => 'ชาย',
        'title_en' => 'Mr.', 'first_name_en' => 'Somchai', 'last_name_en' => 'Jaidee', 'nickname_en' => 'Chai',
        'date_of_birth' => '1995-01-01',
        'nationality' => 'ไทย', 'ethnicity' => 'ไทย', 'religion' => 'พุทธ', 'gender' => 'male',
        'height_cm' => 170, 'weight_kg' => 65,
        'has_congenital_disease' => false, 'has_criminal_record' => false,
        'country' => 'ไทย', 'hometown' => 'กรุงเทพฯ',
        'email' => 'somchai@example.com', 'phone' => '0812345678',
        'national_id_number' => '1234567890123',
        'current_address' => '123 ถนนสุขุมวิท',
        'living_status' => 'own_home', 'marital_status' => 'single',
        'military_status' => 'exempted', 'has_lese_majeste_record' => false,
        'status' => 'submitted',
    ], $overrides));

    ApplicantEducation::create([
        'applicant_id' => $applicant->id, 'degree_level' => 'bachelor',
        'institution_name' => 'มหาวิทยาลัยทดสอบ', 'province_country' => 'กรุงเทพฯ',
        'start_year' => 2556, 'end_year' => 2560, 'major' => 'วิศวกรรมศาสตร์', 'gpa' => 3.2,
    ]);

    ApplicantLanguage::create([
        'applicant_id' => $applicant->id, 'language' => 'thai',
        'speaking_level' => 5, 'understanding_level' => 5, 'reading_level' => 5, 'writing_level' => 5,
    ]);
    ApplicantLanguage::create([
        'applicant_id' => $applicant->id, 'language' => 'english',
        'speaking_level' => 3, 'understanding_level' => 3, 'reading_level' => 3, 'writing_level' => 3,
    ]);
    ApplicantLanguage::create([
        'applicant_id' => $applicant->id, 'language' => 'chinese',
        'speaking_level' => 0, 'understanding_level' => 0, 'reading_level' => 0, 'writing_level' => 0,
    ]);

    ApplicantWorkHistory::create([
        'applicant_id' => $applicant->id, 'order_no' => 1,
        'company_name' => 'บริษัท ทดสอบ จำกัด', 'position' => 'พนักงาน',
        'start_year' => 2561, 'end_year' => 2563, 'supervisor_name' => 'หัวหน้า', 'last_salary' => 20000,
    ]);

    ApplicantReference::create([
        'applicant_id' => $applicant->id, 'order_no' => 1,
        'name' => 'อ้างอิง หนึ่ง', 'relationship' => 'เพื่อน', 'workplace_address' => 'ที่ทำงาน', 'phone' => '0899999999',
    ]);

    ApplicantDocument::create([
        'applicant_id' => $applicant->id, 'document_type' => 'photo',
        'file_path' => 'applicant-documents/'.$applicant->id.'/photo.jpg', 'original_filename' => 'photo.jpg',
    ]);

    return $applicant->fresh();
}

test('summary applicant detail page loads and shows key sections', function () {
    Storage::fake('local');

    $admin = makeAdmin();
    $applicant = makeFullApplicant();
    Storage::disk('local')->put($applicant->documents->first()->file_path, 'fake-image-content');

    $response = $this->actingAs($admin)->get(route('admin.summary.applicants.show', $applicant));

    $response->assertOk();
    $response->assertSeeVolt('pages.admin.summary-applicant-detail');
    $response->assertSee('สถานะการดำเนินการของผู้สมัคร');
    $response->assertSee('สมชาย');
    $response->assertSee('photo.jpg');
    $response->assertSee('ดูตัวอย่าง/ดาวน์โหลด');
    $response->assertSee('บันทึกการแก้ไข');
    $response->assertDontSee('อนุมัติ');
});

test('dashboard applicant detail link still points at the original approve/reject page', function () {
    $admin = makeAdmin();
    $applicant = makeFullApplicant();

    $response = $this->actingAs($admin)->get(route('admin.applicants.show', $applicant));

    $response->assertOk();
    $response->assertSee('อนุมัติ');
    $response->assertSee('ปฏิเสธ');
});

test('status stepper colors match each applicant status', function (string $status, array $expectedColors) {
    $admin = makeAdmin();
    $applicant = makeFullApplicant(['status' => $status]);

    $this->actingAs($admin);

    $component = Volt::test('pages.admin.summary-applicant-detail', ['applicant' => $applicant]);
    $steps = $component->instance()->statusSteps();

    expect(array_column($steps, 'color'))->toBe($expectedColors);
})->with([
    'submitted' => ['submitted', ['yellow', 'gray', 'gray', 'gray', 'gray']],
    'qualified' => ['qualified', ['green', 'yellow', 'gray', 'gray', 'gray']],
    'disqualified' => ['disqualified', ['green', 'red', 'gray', 'gray', 'gray']],
    'code_generated' => ['code_generated', ['green', 'green', 'yellow', 'gray', 'gray']],
    'awaiting_review' => ['awaiting_review', ['green', 'green', 'green', 'yellow', 'gray']],
    'passed_test' => ['passed_test', ['green', 'green', 'green', 'green', 'yellow']],
    'failed_test' => ['failed_test', ['green', 'green', 'green', 'green', 'red']],
]);

test('admin can edit applicant fields and they persist', function () {
    $admin = makeAdmin();
    $applicant = makeFullApplicant();

    $this->actingAs($admin);

    Volt::test('pages.admin.summary-applicant-detail', ['applicant' => $applicant])
        ->set('phone', '0899999000')
        ->set('special_skills', 'ทดสอบทักษะพิเศษ')
        ->set('workHistories.1.company_name', 'บริษัท ใหม่ จำกัด')
        ->set('references.2.name', 'อ้างอิง สอง')
        ->set('references.2.relationship', 'ญาติ')
        ->set('references.2.workplace_address', 'ที่ทำงาน 2')
        ->set('references.2.phone', '0888888888')
        ->call('save')
        ->assertHasNoErrors();

    $applicant->refresh();
    expect($applicant->phone)->toBe('0899999000');
    expect($applicant->special_skills)->toBe('ทดสอบทักษะพิเศษ');
    expect($applicant->workHistories()->where('order_no', 1)->first()->company_name)->toBe('บริษัท ใหม่ จำกัด');
    expect($applicant->references()->where('order_no', 2)->first()->name)->toBe('อ้างอิง สอง');
});

test('admin can upload a new document which replaces the old file', function () {
    Storage::fake('local');

    $admin = makeAdmin();
    $applicant = makeFullApplicant();
    $oldPath = $applicant->documents->first()->file_path;
    Storage::disk('local')->put($oldPath, 'old-content');

    $this->actingAs($admin);

    $newFile = UploadedFile::fake()->image('new-photo.jpg');

    Volt::test('pages.admin.summary-applicant-detail', ['applicant' => $applicant])
        ->set('documents.photo', $newFile)
        ->call('save')
        ->assertHasNoErrors();

    Storage::disk('local')->assertMissing($oldPath);

    $doc = ApplicantDocument::where('applicant_id', $applicant->id)->where('document_type', 'photo')->first();
    expect($doc->original_filename)->toBe('new-photo.jpg');
    Storage::disk('local')->assertExists($doc->file_path);
});

test('ID back number field is gone from the applicant form, admin form and export, and stored values stay untouched', function () {
    $admin = makeAdmin();
    $applicant = makeFullApplicant();
    $applicant->forceFill(['national_id_back_number' => 'JT1234567890'])->save();

    // ฟอร์มผู้สมัคร
    Volt::test('pages.application.personal-background')
        ->assertSee('เลขบัตรประชาชน')
        ->assertDontSee('เลขหลังบัตร')
        ->set('locale', 'en')
        ->assertDontSee('ID Back Number');

    // ฟอร์มแอดมิน + บันทึกต้องไม่แตะค่าเดิมในฐานข้อมูล
    $this->actingAs($admin);
    Volt::test('pages.admin.summary-applicant-detail', ['applicant' => $applicant])
        ->assertDontSee('เลขหลังบัตร')
        ->set('phone', '0899999000')
        ->call('save')
        ->assertHasNoErrors();

    expect($applicant->fresh()->national_id_back_number)->toBe('JT1234567890');

    // ไฟล์ส่งออก
    $summary = Volt::test('pages.admin.summary')->instance();
    $columns = (new ReflectionMethod($summary, 'exportColumns'))->invoke($summary);
    expect(array_keys($columns))->not->toContain('เลขหลังบัตรประชาชน')
        ->and(array_keys($columns))->toContain('เลขบัตรประชาชน');
});

test('financial information fields are removed everywhere and the shift label uses the new wording', function () {
    $shiftLabel = 'การเข้ากะมีความจำเป็นเฉพาะบางตำแหน่งงาน เช่น ผู้ควบคุมงานในไลน์ผลิต';
    $admin = makeAdmin();
    $applicant = makeFullApplicant([
        'shift_work_ok' => true,
        'illness_or_accident_history' => 'ไม่มี',
        'informal_debt_amount' => 1234,
    ]);

    // ฟอร์มผู้สมัคร (ขั้นตอนข้อมูลอื่นๆ) ต้องไม่มีหัวข้อการเงิน
    Volt::test('pages.application.personal-background')
        ->set('step', 'other')
        ->assertDontSee('ข้อมูลด้านการเงิน')
        ->assertDontSee('หนี้นอกระบบ')
        ->assertSee($shiftLabel);

    // ฟอร์มแอดมิน
    $this->actingAs($admin);
    Volt::test('pages.admin.summary-applicant-detail', ['applicant' => $applicant])
        ->assertDontSee('ข้อมูลด้านการเงิน')
        ->assertDontSee('ยอดค้างชำระ')
        ->assertSee($shiftLabel)
        ->call('save')
        ->assertHasNoErrors();

    // ค่าเดิมในฐานข้อมูลต้องไม่ถูกแตะ
    expect($applicant->fresh()->informal_debt_amount)->toBe('1234.00');

    // ไฟล์ส่งออก
    $summary = Volt::test('pages.admin.summary')->instance();
    $headers = array_keys((new ReflectionMethod($summary, 'exportColumns'))->invoke($summary));
    expect($headers)->toContain($shiftLabel)
        ->not->toContain('ทำงานเป็นกะหมุนเวียนได้')
        ->not->toContain('ยอดค้างชำระ (บาท)')
        ->not->toContain('หนี้นอกระบบ (บาท/เดือน)');

    // PDF และหน้าตรวจทาน
    $pdfHtml = view('pdf.application', ['applicant' => $applicant->fresh()->load(['position', 'education', 'languages', 'workHistories', 'references', 'documents'])])->render();
    expect($pdfHtml)->toContain($shiftLabel)
        ->not->toContain('ชำระหนี้นอกระบบ')
        ->not->toContain('บัตรเครดิต/บัตรกดเงินสด')
        ->toContain('ในระยะเวลา 3 ปีที่ผ่านมา');

    $reviewHtml = view('partials.applicant-review', ['a' => $applicant->fresh()->load(['position', 'education', 'languages', 'workHistories', 'references', 'documents'])])->render();
    expect($reviewHtml)->toContain($shiftLabel)->not->toContain('ทำงานเป็นกะหมุนเวียนได้');
});

test('pdf shows nicknames in their own columns, not in parentheses after the name', function () {
    $relations = ['position', 'education', 'languages', 'workHistories', 'references', 'documents'];

    $with = makeFullApplicant(['first_name_th' => 'กิตติทัศน์', 'last_name_th' => 'เจ็กสูงเนิน', 'nickname_th' => 'ปอ', 'nickname_en' => 'por']);
    $html = view('pdf.application', ['applicant' => $with->load($relations)])->render();
    expect($html)->toContain('กิตติทัศน์ เจ็กสูงเนิน')
        ->toContain('Nickname (TH)')->toContain('ปอ')
        ->toContain('Nickname (EN)')->toContain('por')
        ->not->toContain('เจ็กสูงเนิน (ปอ)')->not->toContain('(por)');

    $without = makeFullApplicant(['nickname_th' => '', 'nickname_en' => '']);
    $html = view('pdf.application', ['applicant' => $without->load($relations)])->render();
    expect($html)->not->toContain('()')->not->toContain('( )')->toContain('Nickname (TH)');
});

test('pdf shows the number of children only when it was filled in', function () {
    $relations = ['position', 'education', 'languages', 'workHistories', 'references', 'documents'];
    $childrenValue = function ($applicant) use ($relations): string {
        $html = view('pdf.application', ['applicant' => $applicant->load($relations)])->render();
        preg_match('/Number of children<\/div>\s*<div class="f-value">(.*?)<\/div>/s', $html, $m);

        return trim($m[1] ?? 'NOT-FOUND');
    };

    expect($childrenValue(makeFullApplicant(['spouse_name' => null, 'children_count' => null])))->toBe('')
        ->and($childrenValue(makeFullApplicant(['spouse_name' => 'สมหญิง', 'children_count' => 2])))->toBe('2 คน')
        ->and($childrenValue(makeFullApplicant(['children_count' => 0])))->toBe('0 คน');
});

test('pdf family section has one column each for spouse occupation and for father and mother details', function () {
    $relations = ['position', 'education', 'languages', 'workHistories', 'references', 'documents'];
    $applicant = makeFullApplicant([
        'spouse_name' => 'สมหญิง', 'spouse_occupation' => 'ครูสอนดนตรี',
        'father_name' => 'วุฒิชัย', 'father_age' => 47, 'father_occupation' => 'รับจ้าง', 'father_alive' => true,
        'mother_name' => 'นนทกร', 'mother_age' => 45, 'mother_occupation' => 'ค้าขาย', 'mother_alive' => false,
    ]);
    $html = view('pdf.application', ['applicant' => $applicant->load($relations)])->render();

    expect($html)->toContain('อาชีพคู่สมรส / Spouse')->toContain('ครูสอนดนตรี')
        ->toContain('อายุบิดา (ปี)')->toContain('อาชีพบิดา')->toContain('สถานะบิดา')->toContain('มีชีวิต')
        ->toContain('อายุมารดา (ปี)')->toContain('อาชีพมารดา')->toContain('สถานะมารดา')->toContain('ถึงแก่กรรม')
        ->not->toContain('(47 ปี)');
});

test('pdf footer: known person shows dash for the missing half, consent first, single signature with thai date', function () {
    $relations = ['position', 'education', 'languages', 'workHistories', 'references', 'documents'];
    $render = fn ($applicant) => view('pdf.application', ['applicant' => $applicant->load($relations)])->render();
    $knownPerson = '/ความสัมพันธ์<\/div><div class="f-value">(.*?)<\/div>/su';

    // ไม่กรอกทั้งสองช่อง: ไม่แสดงอะไร
    preg_match($knownPerson, $render(makeFullApplicant()), $m);
    expect(trim($m[1]))->toBe('');

    // กรอกช่องเดียว: อีกช่องเป็น -
    preg_match($knownPerson, $render(makeFullApplicant(['known_person_in_company' => 'สมศักดิ์'])), $m);
    expect(trim($m[1]))->toBe('สมศักดิ์ / -');
    preg_match($knownPerson, $render(makeFullApplicant(['known_person_relation' => 'เพื่อน'])), $m);
    expect(trim($m[1]))->toBe('- / เพื่อน');

    // ข้อความยินยอม: [ยอมรับแล้ว] อยู่หน้าข้อความ, ช่องเซ็นเดียว, วันที่ พ.ศ.
    $html = $render(makeFullApplicant([
        'data_accuracy_consent' => true, 'privacy_policy_consent' => true,
        'signature_name' => 'ดร. นนทิวัฒน์ เพ็งพันธ์', 'signed_at' => '2026-11-17',
    ]));
    expect($html)->toMatch('/\[ยอมรับแล้ว\]<\/span>\s*ข้าพเจ้าขอรับรองว่า/u')
        ->toMatch('/\[ยอมรับแล้ว\]<\/span>\s*ข้าพเจ้าได้อ่านนโยบาย/u')
        ->toContain('วันที่ 17 พฤศจิกายน พ.ศ. 2569')
        ->and(substr_count($html, "Applicant's Signature"))->toBe(1);
});
