<?php

return [

    /*
    | เวอร์ชันของนโยบายคุ้มครองข้อมูลส่วนบุคคลที่แสดงใน modal หน้าแรก
    | (resources/views/partials/privacy-policy-employee.blade.php)
    |
    | เมื่อปรับเนื้อหานโยบาย ให้เปลี่ยนค่านี้ — ผู้สมัครที่ยินยอมฉบับเก่าไว้ใน session
    | จะต้องยินยอมฉบับใหม่ก่อนเข้าหน้ากรอกใบสมัคร และใบสมัครจะบันทึกเวอร์ชันที่ยินยอมไว้
    */
    'policy_version' => 'QMR-GSK-039 Rev.01',

    // ชื่อ key ใน session ที่เก็บการยินยอม
    'session_key' => 'pdpa_consent',

];
