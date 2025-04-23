<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-indigo-600">ورود به کتابستان</h1>
        <p class="text-gray-500 mt-2">وارد حساب کاربری خود شوید</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- ایمیل -->
        <div>
            <x-input-label for="email" :value="__('ایمیل')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="example@domain.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- رمز عبور -->
        <div class="mt-4">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('رمز عبور')" />
                @if (Route::has('password.request'))
                    <a class="text-sm text-indigo-600 hover:text-indigo-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('فراموشی رمز عبور؟') }}
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" placeholder="********" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- مرا به خاطر بسپار -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="mr-2 text-sm text-gray-600">{{ __('مرا به خاطر بسپار') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <div>
                @if (Route::has('register'))
                    <span class="text-sm text-gray-600">حساب کاربری ندارید؟</span>
                    <a class="text-sm text-indigo-600 hover:text-indigo-900 mr-1" href="{{ route('register') }}">
                        {{ __('ثبت نام') }}
                    </a>
                @endif
            </div>

            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                {{ __('ورود') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
