<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * บังคับให้ผู้สมัครยินยอมนโยบายคุ้มครองข้อมูลส่วนบุคคลที่หน้าแรกก่อนเข้าหน้ากรอกใบสมัคร
 * หากยังไม่ยินยอม (หรือยินยอมนโยบายเวอร์ชันเก่า) จะถูกส่งกลับหน้าแรกพร้อมเปิด modal นโยบายอัตโนมัติ
 */
class EnsurePolicyAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        $consent = $request->session()->get(config('privacy.session_key'));

        if (($consent['version'] ?? null) !== config('privacy.policy_version')) {
            return redirect('/')->with('open_policy', true);
        }

        return $next($request);
    }
}
