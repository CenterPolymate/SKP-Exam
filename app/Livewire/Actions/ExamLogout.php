<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ExamLogout
{
    /**
     * Log the current exam applicant out of the application.
     */
    public function __invoke(): void
    {
        Auth::guard('exam_applicant')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
