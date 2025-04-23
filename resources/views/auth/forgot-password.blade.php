<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-indigo-600">بازیابی رمز عبور</h1>
    </div>

    <div class="mb-4 text-sm text-gray-600 text-justify">
        {{ __('رمز عبور خود را فراموش کرده‌اید؟ مشکلی نیست. فقط ایمیل خود را وارد کنید تا لینک بازیابی رمز عبور را برای شما ارسال کنیم.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- ایمیل -->
        <div>
            <x-input-label for="email" :value="__('ایمیل')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="example@domain.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                بازگشت به صفحه ورود
            </a>

            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                {{ __('ارسال لینک بازیابی') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
