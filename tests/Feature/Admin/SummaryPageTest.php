<?php

use App\Models\Applicant;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Volt\Volt;

function makeSummaryAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function makeApplicantSubmittedAt(string $date): Applicant
{
    $position = Position::create(['title' => 'Test Position '.uniqid(), 'is_active' => true]);

    $applicant = Applicant::create([
        'position_id' => $position->id,
        'expected_salary' => 25000,
        'title_th' => 'นาย', 'first_name_th' => 'สมชาย', 'last_name_th' => 'ใจดี', 'nickname_th' => 'ชาย',
        'title_en' => 'Mr.', 'first_name_en' => 'Somchai', 'last_name_en' => 'Jaidee', 'nickname_en' => 'Chai',
        'date_of_birth' => '1995-01-01',
        'nationality' => 'ไทย', 'ethnicity' => 'ไทย', 'religion' => 'พุทธ', 'gender' => 'male',
        'height_cm' => 170, 'weight_kg' => 65,
        'has_congenital_disease' => false, 'has_criminal_record' => false,
        'country' => 'ไทย', 'hometown' => 'กรุงเทพฯ',
        'email' => uniqid().'@example.com', 'phone' => '0812345678',
        'national_id_number' => (string) random_int(1000000000000, 9999999999999),
        'current_address' => '123 ถนนสุขุมวิท',
        'living_status' => 'own_home', 'marital_status' => 'single',
        'military_status' => 'exempted', 'has_lese_majeste_record' => false,
        'status' => 'submitted',
    ]);

    $applicant->created_at = Carbon::parse($date);
    $applicant->save();

    return $applicant;
}

test('summary page loads and lists distinct applied years across database engines', function () {
    $admin = makeSummaryAdmin();

    makeApplicantSubmittedAt('2024-03-01');
    makeApplicantSubmittedAt('2025-06-01');
    makeApplicantSubmittedAt('2025-11-01');

    $component = Volt::actingAs($admin)->test('pages.admin.summary');

    $component->assertOk();

    expect($component->get('appliedYears')->all())->toBe([2025, 2024]);
});

test('summary page is reachable over HTTP and renders the applicants table', function () {
    $admin = makeSummaryAdmin();
    makeApplicantSubmittedAt('2026-01-01');

    $response = $this->actingAs($admin)->get('/admin/summary');

    $response->assertOk();
    $response->assertSeeVolt('pages.admin.summary');
});
