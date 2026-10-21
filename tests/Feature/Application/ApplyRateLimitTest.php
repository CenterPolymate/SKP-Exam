<?php

use App\Models\Applicant;
use App\Models\Position;
use Livewire\Volt\Volt;

function validPersonalPayload(Position $position): array
{
    return [
        'position_id' => $position->id,
        'expected_salary' => 25000,
        'title_th' => 'นาย', 'first_name_th' => 'ทดสอบ', 'last_name_th' => 'สคริปต์', 'nickname_th' => 'ทด',
        'title_en' => 'Mr.', 'first_name_en' => 'Test', 'last_name_en' => 'Script', 'nickname_en' => 'Ted',
        'date_of_birth' => '1995-01-01',
        'nationality' => 'ไทย', 'ethnicity' => 'ไทย', 'religion' => 'พุทธ',
        'gender' => 'male', 'height_cm' => 170, 'weight_kg' => 65,
        'has_congenital_disease' => '0', 'has_criminal_record' => '0',
        'country' => 'ไทย', 'hometown' => 'กรุงเทพฯ',
        'email' => 'test@example.com', 'phone' => '0812345678',
        'national_id_number' => '1234567890123',
        'current_address' => '123 ถนนสุขุมวิท',
        'living_status' => 'own_home', 'marital_status' => 'single',
        'father_name' => 'พ่อ', 'father_age' => 50, 'father_occupation' => 'ค้าขาย', 'father_alive' => '1',
        'mother_name' => 'แม่', 'mother_age' => 48, 'mother_occupation' => 'ค้าขาย', 'mother_alive' => '1',
        'military_status' => 'exempted', 'has_lese_majeste_record' => '0',
    ];
}

test('submitting a new application is rate limited per IP', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);

    foreach (range(1, 10) as $i) {
        Volt::test('pages.application.personal-background')
            ->set(validPersonalPayload($position))
            ->call('submitPersonal')
            ->assertHasNoErrors()
            ->assertSet('step', 'education');
    }

    expect(Applicant::count())->toBe(10);

    // the 11th new application from the same IP should be blocked
    Volt::test('pages.application.personal-background')
        ->set(validPersonalPayload($position))
        ->call('submitPersonal')
        ->assertStatus(429);

    expect(Applicant::count())->toBe(10);
});

test('updating an existing application in progress is not rate limited', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);

    $component = Volt::test('pages.application.personal-background')
        ->set(validPersonalPayload($position))
        ->call('submitPersonal')
        ->assertHasNoErrors();

    $applicantId = $component->get('applicantId');

    // going back and resubmitting the same in-progress application repeatedly
    // must not count against the "new applicant" rate limit
    foreach (range(1, 15) as $i) {
        $component->set('step', 'personal')
            ->set(validPersonalPayload($position))
            ->set('applicantId', $applicantId)
            ->call('submitPersonal')
            ->assertHasNoErrors()
            ->assertSet('step', 'education');
    }

    expect(Applicant::count())->toBe(1);
});
