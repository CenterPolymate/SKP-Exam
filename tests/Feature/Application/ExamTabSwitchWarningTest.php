<?php

use App\Models\ExamAssignment;
use App\Models\ExamAttempt;
use App\Models\User;

// ใช้ makeExamWithQuestions() จาก ExamDuplicateTest และ makeFullApplicant() จาก SummaryApplicantDetailTest
function startedAttemptFor(ExamAssignment $assignment, int $adminId): ExamAttempt
{
    $exam = makeExamWithQuestions(User::find($adminId));

    return ExamAttempt::create([
        'exam_assignment_id' => $assignment->id,
        'exam_id' => $exam->id,
        'started_at' => now(),
        'status' => 'in_progress',
    ]);
}

function makeAssignment(int $adminId, string $username = 'APP-000001'): ExamAssignment
{
    return ExamAssignment::create([
        'applicant_id' => makeFullApplicant()->id,
        'username' => $username,
        'password_encrypted' => encrypt('secret123'),
        'exam_deadline' => now()->addDays(3)->toDateString(),
        'created_by' => $adminId,
    ]);
}

test('exam page warns the applicant when they come back after leaving the page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $assignment = makeAssignment($admin->id);
    $attempt = startedAttemptFor($assignment, $admin->id);

    $html = $this->actingAs($assignment, 'exam_applicant')
        ->get(route('exam.attempts.take', $attempt))
        ->assertOk()
        ->assertSee('ท่านออกจากหน้าทำข้อสอบ')
        ->assertSee('รับทราบและทำข้อสอบต่อ')
        ->getContent();

    // ตัวดักใช้ directive ของ Alpine (ถูกถอดเองเมื่อออกจากหน้า) ไม่ใช่ document.addEventListener ที่ค้างอยู่
    expect($html)->toContain('visibilitychange.document="onVisibilityChange()"')
        ->not->toContain("document.addEventListener('visibilitychange'")
        ->toContain('id="tab-switch-warning-modal"')
        ->toContain('tabSwitches: 0');
});

test('warning counter starts from the number of switches already recorded', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $assignment = makeAssignment($admin->id);
    $attempt = startedAttemptFor($assignment, $admin->id);
    $attempt->update(['suspicious_activity_count' => 2]);

    $this->actingAs($assignment, 'exam_applicant')
        ->get(route('exam.attempts.take', $attempt))
        ->assertOk()
        ->assertSee('tabSwitches: 2', false);
});

test('tab switch reports are counted and logged for the owner only', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $assignment = makeAssignment($admin->id);
    $attempt = startedAttemptFor($assignment, $admin->id);

    $this->actingAs($assignment, 'exam_applicant')
        ->postJson(route('exam.attempts.suspicious-activity', $attempt), ['type' => 'tab_switch'])
        ->assertOk()->assertJson(['ok' => true]);
    $this->postJson(route('exam.attempts.suspicious-activity', $attempt), ['type' => 'tab_switch'])->assertOk();

    $attempt->refresh();
    expect($attempt->suspicious_activity_count)->toBe(2)
        ->and($attempt->suspicious_activity_log)->toHaveCount(2)
        ->and($attempt->suspicious_activity_log[0]['type'])->toBe('tab_switch');

    // ประเภทที่ไม่รู้จักถูกปฏิเสธ
    $this->postJson(route('exam.attempts.suspicious-activity', $attempt), ['type' => 'whatever'])->assertStatus(422);

    // ผู้สอบคนอื่นรายงานในชื่อการสอบของคนนี้ไม่ได้
    $other = makeAssignment($admin->id, 'APP-000002');
    $this->actingAs($other, 'exam_applicant')
        ->postJson(route('exam.attempts.suspicious-activity', $attempt), ['type' => 'tab_switch'])
        ->assertForbidden();
    expect($attempt->fresh()->suspicious_activity_count)->toBe(2);
});

test('urls in a question become safe clickable links and nothing else is rendered as html', function () {
    $html = \App\Support\Linkify::toHtml("ทำแบบทดสอบที่ https://forms.gle/7Hv2aGW26oiRUAag7. แล้วแนบภาพ <script>alert(1)</script>");

    expect($html)->toContain('<a href="https://forms.gle/7Hv2aGW26oiRUAag7" target="_blank" rel="noopener noreferrer" data-exam-link')
        ->toContain('>https://forms.gle/7Hv2aGW26oiRUAag7</a>.') // จุดท้ายประโยคอยู่นอกลิงก์
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->not->toContain('<script>')
        ->and(\App\Support\Linkify::toHtml('javascript:alert(1) ftp://x.com'))->not->toContain('<a ')
        ->and(\App\Support\Linkify::toHtml('ข้อความธรรมดา'))->toBe('ข้อความธรรมดา');
});

test('exam page renders the question link as clickable and wires the link-click handler', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $assignment = makeAssignment($admin->id);
    $attempt = startedAttemptFor($assignment, $admin->id);
    $attempt->exam->questions()->first()->update(['question_text' => 'ทำที่ https://forms.gle/abc123']);

    $this->actingAs($assignment, 'exam_applicant')
        ->get(route('exam.attempts.take', $attempt))
        ->assertOk()
        ->assertSee('<a href="https://forms.gle/abc123" target="_blank" rel="noopener noreferrer" data-exam-link', false)
        ->assertSee('onExamLinkClick($event)', false)
        ->assertSee('leftForExamLink', false);
});

test('opening a question link is logged but does not count as suspicious activity', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $assignment = makeAssignment($admin->id);
    $attempt = startedAttemptFor($assignment, $admin->id);

    $this->actingAs($assignment, 'exam_applicant')
        ->postJson(route('exam.attempts.suspicious-activity', $attempt), ['type' => 'exam_link_opened', 'url' => 'https://forms.gle/abc123'])
        ->assertOk();

    $attempt->refresh();
    expect($attempt->suspicious_activity_count)->toBe(0)
        ->and($attempt->suspicious_activity_log)->toHaveCount(1)
        ->and($attempt->suspicious_activity_log[0]['type'])->toBe('exam_link_opened')
        ->and($attempt->suspicious_activity_log[0]['url'])->toBe('https://forms.gle/abc123');

    // สลับแท็บจริงยังนับตามปกติ
    $this->postJson(route('exam.attempts.suspicious-activity', $attempt), ['type' => 'tab_switch'])->assertOk();
    expect($attempt->fresh()->suspicious_activity_count)->toBe(1);
});

test('admin grading page lists opened question links separately from suspicious signals', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $assignment = makeAssignment($admin->id);
    $attempt = startedAttemptFor($assignment, $admin->id);
    $attempt->update([
        'suspicious_activity_count' => 1,
        'suspicious_activity_log' => [
            ['type' => 'exam_link_opened', 'url' => 'https://forms.gle/abc123', 'at' => '2026-11-24 10:00:00'],
            ['type' => 'tab_switch', 'at' => '2026-11-24 10:05:00'],
        ],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.exam-review.grade', $attempt))
        ->assertOk()
        ->assertSee('ผู้สมัครเปิดลิงก์ในโจทย์ 1 ครั้ง')
        ->assertSee('https://forms.gle/abc123')
        ->assertSee('พบสัญญาณน่าสงสัยระหว่างทำข้อสอบ 1 ครั้ง')
        ->assertSee('สลับแท็บ/หน้าต่าง');
});
