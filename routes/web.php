<?php

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

Route::view('profile', 'profile')
    ->middleware(['auth', 'admin'])
    ->name('profile');

require __DIR__.'/auth.php';
