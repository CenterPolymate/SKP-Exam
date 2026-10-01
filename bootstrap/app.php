<?php

use App\Http\Middleware\EnsureExamNotExpired;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'exam.not_expired' => EnsureExamNotExpired::class,
        ]);

        // landingLang ถูกตั้งค่าฝั่ง client ด้วย JS ธรรมดา (Alpine) จึงไม่เข้ารหัส
        // เพื่อให้ Laravel อ่านค่าได้ตรงกับที่ browser ส่งมา
        $middleware->encryptCookies(except: ['landingLang']);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('exam/*')
            ? route('exam.login')
            : route('login'));

        $middleware->redirectUsersTo(fn (Request $request) => $request->is('exam/*')
            ? route('exam.dashboard')
            : route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
