<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamAttemptController extends Controller
{
    // แยกออกจาก Livewire component ของหน้าทำข้อสอบ เพื่อไม่ให้ request นี้ไปแย่ง/ชน
    // กับ request ของการเปลี่ยนหน้าข้อ (next/previous) จนทำให้ Alpine state บนหน้าทำข้อสอบเพี้ยน
    public function logSuspiciousActivity(Request $request, ExamAttempt $attempt): JsonResponse
    {
        abort_unless($attempt->exam_assignment_id === Auth::guard('exam_applicant')->id(), 403);

        $data = $request->validate([
            'type' => ['required', 'string', 'in:tab_switch,fullscreen_exit'],
        ]);

        $log = $attempt->suspicious_activity_log ?? [];
        $log[] = ['type' => $data['type'], 'at' => now()->toDateTimeString()];

        $attempt->update([
            'suspicious_activity_count' => $attempt->suspicious_activity_count + 1,
            'suspicious_activity_log' => $log,
        ]);

        return response()->json(['ok' => true]);
    }
}
