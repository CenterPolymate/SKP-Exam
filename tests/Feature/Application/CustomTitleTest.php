<?php

use App\Models\Applicant;
use App\Models\Position;
use Livewire\Volt\Volt;

// ใช้ validPersonalPayload() จาก ApplyRateLimitTest.php

test('selecting a preset title stores it directly', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);

    Volt::test('pages.application.personal-background')
        ->set(validPersonalPayload($position))
        ->set('title_th_choice', 'นางสาว')
        ->set('title_en_choice', 'Ms.')
        ->assertSet('title_th', 'นางสาว')
        ->assertSet('title_en', 'Ms.');
});

test('selecting other shows a custom input and stores the typed title', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);

    Volt::test('pages.application.personal-background')
        ->set(validPersonalPayload($position))
        ->set('title_th_choice', 'other')
        ->assertSee('ระบุคำนำหน้า')
        ->set('title_th_other', 'ร้อยตรี')
        ->set('title_en_choice', 'other')
        ->set('title_en_other', 'Dr.')
        ->assertSet('title_th', 'ร้อยตรี')
        ->assertSet('title_en', 'Dr.')
        ->call('submitPersonal')
        ->assertHasNoErrors()
        ->assertSet('step', 'education');

    $applicant = Applicant::firstOrFail();
    expect($applicant->title_th)->toBe('ร้อยตรี')
        ->and($applicant->title_en)->toBe('Dr.');
});

test('other with an empty custom title fails validation', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);

    Volt::test('pages.application.personal-background')
        ->set(validPersonalPayload($position))
        ->set('title_th_choice', 'other')
        ->call('submitPersonal')
        ->assertHasErrors(['title_th' => 'required']);
});

test('english mode hides Thai name fields and copies English names into the Thai columns', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);

    Volt::test('pages.application.personal-background')
        ->set('locale', 'en')
        ->assertSet('locale', 'en')
        ->assertDontSee('Title (TH)')
        ->assertDontSee('Nickname (TH)')
        ->set(array_merge(validPersonalPayload($position), [
            'title_th' => '', 'first_name_th' => '', 'last_name_th' => '', 'nickname_th' => '',
        ]))
        ->set('title_en_choice', 'other')
        ->set('title_en_other', 'Dr.')
        ->call('submitPersonal')
        ->assertHasNoErrors()
        ->assertSet('step', 'education');

    $applicant = Applicant::firstOrFail();
    expect($applicant->title_th)->toBe('Dr.')
        ->and($applicant->first_name_th)->toBe('Test')
        ->and($applicant->last_name_th)->toBe('Script')
        ->and($applicant->nickname_th)->toBe('Ted');
});

test('thai mode still shows Thai name fields', function () {
    Volt::test('pages.application.personal-background')
        ->assertSet('locale', 'th')
        ->assertSee('คำนำหน้า (TH)');
});

test('full name accessors use the custom title and never the word other', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);
    $base = validPersonalPayload($position);

    $custom = Applicant::create(array_merge($base, [
        'title_th' => 'ดร.', 'first_name_th' => 'นนทิวัฒน์', 'last_name_th' => 'เพ็งพันธ์',
        'title_en' => 'Dr.', 'first_name_en' => 'Nontiwat', 'last_name_en' => 'Pengpan',
    ]));
    $preset = Applicant::create(array_merge($base, [
        'title_th' => 'นาย', 'first_name_th' => 'สมชาย', 'last_name_th' => 'ใจดี',
    ]));

    expect($custom->full_name_th)->toBe('ดร. นนทิวัฒน์ เพ็งพันธ์')
        ->and($custom->full_name_en)->toBe('Dr. Nontiwat Pengpan')
        ->and($preset->full_name_th)->toBe('นายสมชาย ใจดี');
});

test('admin pages show the custom title in the applicant name', function () {
    $admin = makeAdmin();
    $applicant = makeFullApplicant([
        'title_th' => 'ดร.', 'first_name_th' => 'นนทิวัฒน์', 'last_name_th' => 'เพ็งพันธ์',
        'title_en' => 'Dr.',
    ]);

    foreach (['/dashboard', '/admin/summary', "/admin/summary/applicants/{$applicant->id}"] as $url) {
        $this->actingAs($admin)->get($url)
            ->assertOk()
            ->assertSee('ดร. นนทิวัฒน์ เพ็งพันธ์')
            ->assertDontSee('อื่นๆ นนทิวัฒน์')
            ->assertDontSee('other นนทิวัฒน์');
    }
});

test('admin edit form loads a custom title into the other field and saves changes', function () {
    $admin = makeAdmin();
    $applicant = makeFullApplicant(['title_th' => 'ร้อยตรี', 'title_en' => 'Dr.']);

    Volt::actingAs($admin)->test('pages.admin.summary-applicant-detail', ['applicant' => $applicant])
        ->assertSet('title_th_choice', 'other')
        ->assertSet('title_th_other', 'ร้อยตรี')
        ->assertSet('title_en_choice', 'other')
        ->set('title_th_other', 'ดร.')
        ->assertSet('title_th', 'ดร.');
});

test('gender "other" option is labelled as prefer not to say in both languages', function () {
    Volt::test('pages.application.personal-background')
        ->assertSee('ไม่ระบุเพศ')
        ->set('locale', 'en')
        ->assertSee('Prefer not to say');
});

test('shift work question uses the new wording in both languages', function () {
    $th = 'การเข้ากะมีความจำเป็นเฉพาะบางตำแหน่งงาน เช่น ผู้ควบคุมงานในไลน์ผลิต';
    $en = 'Shift work is required only for certain positions, such as production line supervisors';

    $component = Volt::test('pages.application.personal-background');
    expect($component->instance()->tr('shift_label'))->toBe($th);

    $component->set('locale', 'en');
    expect($component->instance()->tr('shift_label'))->toBe($en);
});

test('source channel is required on the other-info step', function () {
    Volt::test('pages.application.personal-background')
        ->set('step', 'other')
        ->assertSee('ช่องทางที่ทราบข่าวการรับสมัคร')
        ->call('submitOther')
        ->assertHasErrors(['source_channels' => 'required']);

    Volt::test('pages.application.personal-background')
        ->set('step', 'other')
        ->set('source_channels', ['JOBTHAI'])
        ->call('submitOther')
        ->assertHasNoErrors(['source_channels']);
});
