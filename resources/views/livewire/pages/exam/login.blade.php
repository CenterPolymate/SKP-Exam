<?php

use App\Livewire\Forms\ExamLoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.exam-login')] class extends Component
{
    public ExamLoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirect(route('exam.welcome', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mt-6 text-center">
        <h1 class="text-xl font-bold text-stone-900">ยินดีต้อนรับ</h1>
        <p class="mt-2 text-sm text-stone-500">
            เข้าสู่ระบบเพื่อเริ่มทำแบบทดสอบ<br>
            ใช้ชื่อผู้ใช้และรหัสผ่านที่ได้รับจากทางแอดมิน
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form wire:submit="login" class="mt-6 space-y-4">
        <!-- Username -->
        <div>
            <x-input-label for="username" value="ชื่อผู้ใช้" />
            <x-text-input wire:model="form.username" id="username" class="block mt-1 w-full" type="text" name="username" placeholder="เช่น exam_0012" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="รหัสผ่าน" />
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            placeholder="วางรหัสที่ได้รับ"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <button type="submit" class="w-full rounded-lg bg-[#BA772A] px-4 py-3 font-semibold text-white shadow-sm transition hover:bg-[#a6661f] focus:outline-none focus:ring-2 focus:ring-[#BA772A] focus:ring-offset-2">
            เข้าสู่ระบบ
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-500">
        เข้าระบบไม่ได้?
        <button type="button" @click="contactOpen = true" class="font-medium text-[#BA772A] underline decoration-stone-300 underline-offset-2 hover:text-[#a6661f]">
            ติดต่อเจ้าหน้าที่ HR
        </button>
    </p>
</div>
