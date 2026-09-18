<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.admin-login')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <h1 class="text-2xl font-bold text-stone-900">เข้าสู่ระบบ</h1>
    <p class="mt-1 text-sm text-stone-500">กรอกบัญชีเจ้าหน้าที่เพื่อเข้าใช้งาน</p>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form wire:submit="login" class="mt-8 space-y-5">
        <!-- Username -->
        <div>
            <x-input-label for="username" value="ชื่อผู้ใช้" />
            <x-text-input wire:model="form.username" id="username" class="block mt-1 w-full px-4 py-3" type="text" name="username" placeholder="กรอกชื่อผู้ใช้" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="รหัสผ่าน" />
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full px-4 py-3"
                            type="password"
                            name="password"
                            placeholder="กรอกรหัสผ่าน"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me + Forgot password -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-stone-300 text-[#BA772A] shadow-sm focus:ring-[#BA772A]" name="remember">
                <span class="ms-2 text-sm text-stone-600">จำฉันไว้</span>
            </label>

            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-medium text-[#BA772A] hover:text-[#a6661f] focus:outline-none focus:ring-2 focus:ring-[#BA772A] focus:ring-offset-2" href="{{ route('password.request') }}" wire:navigate>
                    ลืมรหัสผ่าน?
                </a>
            @endif
        </div>

        <button type="submit" class="w-full rounded-lg bg-[#BA772A] px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-[#a6661f] focus:outline-none focus:ring-2 focus:ring-[#BA772A] focus:ring-offset-2">
            เข้าสู่ระบบ
        </button>
    </form>
</div>
