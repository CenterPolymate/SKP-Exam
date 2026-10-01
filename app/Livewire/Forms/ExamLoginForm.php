<?php

namespace App\Livewire\Forms;

use App\Models\ExamAssignment;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ExamLoginForm extends Form
{
    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $assignment = ExamAssignment::where('username', $this->username)->first();
        $decrypted = null;

        if ($assignment) {
            try {
                $decrypted = Crypt::decryptString($assignment->password_encrypted);
            } catch (\Throwable) {
                $decrypted = null;
            }
        }

        if (! $assignment || $decrypted === null || ! hash_equals($decrypted, $this->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
            ]);
        }

        if ($assignment->isExpired()) {
            $assignment->markApplicantFailedIfExpired();

            throw ValidationException::withMessages([
                'form.username' => 'บัญชีนี้หมดอายุการเข้าใช้งานระบบสอบแล้ว',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        Auth::guard('exam_applicant')->login($assignment);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.username' => "พยายามเข้าสู่ระบบผิดพลาดหลายครั้งเกินไป กรุณาลองใหม่อีกครั้งใน {$seconds} วินาที",
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username).'|'.request()->ip());
    }
}
