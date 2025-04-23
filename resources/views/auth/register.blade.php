<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-indigo-600">ثبت نام در کتابستان</h1>
        <p class="text-gray-500 mt-2">ایجاد حساب کاربری جدید</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- نام -->
        <div>
            <x-input-label for="name" :value="__('نام')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="نام و نام خانوادگی" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- ایمیل -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('ایمیل')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="example@domain.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- رمز عبور -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('رمز عبور')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="********" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- تأیید رمز عبور -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('تکرار رمز عبور')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="********" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <div>
                <span class="text-sm text-gray-600">قبلاً ثبت نام کرده‌اید؟</span>
                <a class="text-sm text-indigo-600 hover:text-indigo-900 mr-1" href="{{ route('login') }}">
                    {{ __('ورود') }}
                </a>
            </div>

            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                {{ __('ثبت نام') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
