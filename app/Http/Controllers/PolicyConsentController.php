<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * รับการกดยินยอมนโยบายคุ้มครองข้อมูลส่วนบุคคลจาก modal หน้าแรก
 * แล้วบันทึกลง session เพื่อให้ผ่าน middleware ของหน้า /apply
 * (เวลาและเวอร์ชันจะถูกบันทึกลงใบสมัครตอนสร้างใบสมัครครั้งแรก)
 */
class PolicyConsentController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate(['policy_accepted' => ['accepted']]);

        $request->session()->put(config('privacy.session_key'), [
            'version' => config('privacy.policy_version'),
            'consented_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('application.create');
    }
}
