<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureExamNotExpired
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $assignment = Auth::guard('exam_applicant')->user();

        if ($assignment && $assignment->isExpired()) {
            $assignment->markApplicantFailedIfExpired();

            Auth::guard('exam_applicant')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('exam.login')
                ->with('status', 'บัญชีนี้หมดอายุการเข้าใช้งานระบบสอบแล้ว');
        }

        return $next($request);
    }
}
