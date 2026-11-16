<?php

use App\Models\Applicant;
use App\Models\Position;
use Livewire\Volt\Volt;

function pdpaSession(?string $version = null): array
{
    return [config('privacy.session_key') => [
        'version' => $version ?? config('privacy.policy_version'),
        'consented_at' => '2026-11-16 10:30:00',
    ]];
}

test('apply page redirects to the landing page and opens the policy modal when not yet consented', function () {
    $this->get('/apply')
        ->assertRedirect('/')
        ->assertSessionHas('open_policy', true);

    // หน้าแรกเปิด modal อัตโนมัติเมื่อถูกเด้งกลับมา
    $this->withSession(['open_policy' => true])->get('/')
        ->assertOk()
        ->assertSee('$nextTick(() => openPolicy())', false);

    // เข้าหน้าแรกปกติ ไม่เปิดอัตโนมัติ
    $this->get('/')->assertOk()->assertDontSee('$nextTick(() => openPolicy())', false);
});

test('consent endpoint requires the accepted flag', function () {
    $this->post('/apply/consent')->assertSessionHasErrors('policy_accepted');
    $this->post('/apply/consent', ['policy_accepted' => '0'])->assertSessionHasErrors('policy_accepted');

    $this->get('/apply')->assertRedirect('/');
});

test('consenting stores version and time in the session and unlocks the apply page', function () {
    $this->post('/apply/consent', ['policy_accepted' => '1'])
        ->assertRedirect(route('application.create'))
        ->assertSessionHas(config('privacy.session_key').'.version', config('privacy.policy_version'))
        ->assertSessionHas(config('privacy.session_key').'.consented_at');

    $this->get('/apply')->assertOk();
});

test('consent to an outdated policy version no longer unlocks the apply page', function () {
    $this->withSession(pdpaSession('QMR-GSK-039 Rev.00'))
        ->get('/apply')
        ->assertRedirect('/');
});

test('the landing page consent form posts to the consent endpoint with a csrf token', function () {
    $this->get('/')
        ->assertSee('action="'.route('application.consent').'"', false)
        ->assertSee('name="_token"', false)
        ->assertSee('name="policy_accepted"', false);
});

test('a new application records the policy version and consent time from the session', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);

    $this->withSession(pdpaSession());

    Volt::test('pages.application.personal-background')
        ->set(validPersonalPayload($position))
        ->call('submitPersonal')
        ->assertHasNoErrors();

    $applicant = Applicant::firstOrFail();
    expect($applicant->pdpa_policy_version)->toBe(config('privacy.policy_version'))
        ->and($applicant->pdpa_consented_at->format('Y-m-d H:i:s'))->toBe('2026-11-16 10:30:00');
});

test('consent columns stay empty when there is no consent in the session', function () {
    $position = Position::create(['title' => 'Tester', 'is_active' => true]);

    Volt::test('pages.application.personal-background')
        ->set(validPersonalPayload($position))
        ->call('submitPersonal')
        ->assertHasNoErrors();

    $applicant = Applicant::firstOrFail();
    expect($applicant->pdpa_policy_version)->toBeNull()
        ->and($applicant->pdpa_consented_at)->toBeNull();
});
