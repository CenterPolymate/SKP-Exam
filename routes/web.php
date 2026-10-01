<?php

use App\Http\Controllers\SecureFileController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Volt::route('/apply', 'pages.application.personal-background')
    ->name('application.create');

Volt::route('dashboard', 'pages.admin.dashboard')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('dashboard');

Volt::route('admin/applicants/{applicant}', 'pages.admin.applicant-detail')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.applicants.show');

Volt::route('admin/summary/applicants/{applicant}', 'pages.admin.summary-applicant-detail')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.summary.applicants.show');

Volt::route('admin/positions', 'pages.admin.positions')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.positions.index');

Volt::route('admin/applicant-accounts', 'pages.admin.applicant-accounts')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.applicant-accounts.index');

Volt::route('admin/exams', 'pages.admin.exams')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.exams.index');

Volt::route('admin/exams/create', 'pages.admin.exam-form')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.exams.create');

Volt::route('admin/exams/{exam}/edit', 'pages.admin.exam-form')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.exams.edit');

Volt::route('admin/exam-review', 'pages.admin.exam-review')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.exam-review.index');

Volt::route('admin/exam-review/attempts/{attempt}/grade', 'pages.admin.exam-attempt-grade')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.exam-review.grade');

Volt::route('admin/exam-review/{applicant}', 'pages.admin.exam-review-detail')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.exam-review.show');

Volt::route('admin/summary', 'pages.admin.summary')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.summary.index');

Route::get('secure-files/applicant-documents/{document}', [SecureFileController::class, 'applicantDocument'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('secure-files.applicant-document');

Route::get('secure-files/exam-answers/{answer}', [SecureFileController::class, 'examAnswer'])
    ->middleware(['auth:web,exam_applicant'])
    ->name('secure-files.exam-answer');

Route::view('profile', 'profile')
    ->middleware(['auth', 'admin'])
    ->name('profile');

Volt::route('exam/login', 'pages.exam.login')
    ->middleware('guest:exam_applicant')
    ->name('exam.login');

Volt::route('exam/dashboard', 'pages.exam.dashboard')
    ->middleware(['auth:exam_applicant', 'exam.not_expired'])
    ->name('exam.dashboard');

Volt::route('exam/attempts/{attempt}/start', 'pages.exam.attempt-start')
    ->middleware(['auth:exam_applicant', 'exam.not_expired'])
    ->name('exam.attempts.start');

Volt::route('exam/attempts/{attempt}/take', 'pages.exam.attempt-take')
    ->middleware(['auth:exam_applicant', 'exam.not_expired'])
    ->name('exam.attempts.take');

require __DIR__.'/auth.php';
