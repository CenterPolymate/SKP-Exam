<?php

test('landing page shows the privacy policy modal with the full policy before applying', function () {
    $response = $this->get('/')->assertOk();

    // ปุ่มสมัครงานเปิด modal ไม่ลิงก์ตรงไป /apply
    $response->assertSee('openPolicy()', false)
        ->assertSee('นโยบายคุ้มครองข้อมูลส่วนบุคคลสำหรับพนักงาน')
        ->assertSee('QMR-GSK-039')
        ->assertSee('ข้อมูลส่วนบุคคลชนิดพิเศษ')
        ->assertDontSee('wilailak@skthai.com')
        ->assertSee('จบเอกสาร');

    // ปุ่มถัดไปถูกล็อกจนกว่าจะเลื่อนอ่านจบและติ๊กยอมรับ
    $response->assertSee(':disabled="!policyScrolled"', false)
        ->assertSee(':disabled="!policyAccepted"', false)
        ->assertSee(route('application.consent'), false);
});

test('all 13 policy sections are present', function () {
    $html = view('partials.privacy-policy-employee')->render();

    foreach (range(1, 13) as $n) {
        expect($html)->toContain("{$n}. ");
    }
});
