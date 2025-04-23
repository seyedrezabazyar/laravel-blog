<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('پروفایل کاربری') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header class="mb-6">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('اطلاعات پروفایل') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("اطلاعات پروفایل و آدرس ایمیل خود را بروزرسانی کنید.") }}
                            </p>
                        </header>

                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                            @csrf
                        </form>

                        <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="name" :value="__('نام')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('ایمیل')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div>
                                        <p class="text-sm mt-2 text-gray-800">
                                            {{ __('آدرس ایمیل شما تأیید نشده است.') }}

                                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                {{ __('برای ارسال مجدد ایمیل تأیید اینجا کلیک کنید.') }}
                                            </button>
                                        </p>

                                        @if (session('status') === 'verification-link-sent')
                                            <p class="mt-2 font-medium text-sm text-green-600">
                                                {{ __('یک لینک تأیید جدید به آدرس ایمیل شما ارسال شد.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">{{ __('ذخیره تغییرات') }}</x-primary-button>

                                @if (session('status') === 'profile-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-green-600"
                                    >{{ __('ذخیره شد.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header class="mb-6">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('تغییر رمز عبور') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('برای حفظ امنیت حساب کاربری خود، از رمز عبور طولانی و تصادفی استفاده کنید.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('put')

                            <div>
                                <x-input-label for="update_password_current_password" :value="__('رمز عبور فعلی')" />
                                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="update_password_password" :value="__('رمز عبور جدید')" />
                                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="update_password_password_confirmation" :value="__('تکرار رمز عبور جدید')" />
                                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">{{ __('ذخیره تغییرات') }}</x-primary-button>

                                @if (session('status') === 'password-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-green-600"
                                    >{{ __('ذخیره شد.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section class="space-y-6">
                        <header class="mb-6">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('حذف حساب کاربری') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('پس از حذف حساب کاربری، تمام منابع و داده‌های آن به طور دائمی حذف می‌شوند. قبل از حذف حساب کاربری خود، لطفاً هر گونه اطلاعاتی را که می‌خواهید حفظ کنید، دانلود کنید.') }}
                            </p>
                        </header>

                        <x-danger-button
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                        >{{ __('حذف حساب کاربری') }}</x-danger-button>

                        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                                @csrf
                                @method('delete')

                                <h2 class="text-lg font-medium text-gray-900">آیا از حذف حساب کاربری خود اطمینان دارید؟</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('پس از حذف حساب کاربری، تمام منابع و داده‌های آن به طور دائمی حذف می‌شوند. لطفاً رمز عبور خود را وارد کنید تا تأیید کنید که می‌خواهید حساب کاربری خود را به طور دائمی حذف کنید.') }}
                                </p>

                                <div class="mt-6">
                                    <x-input-label for="password" value="{{ __('رمز عبور') }}" class="sr-only" />

                                    <x-text-input
                                        id="password"
                                        name="password"
                                        type="password"
                                        class="mt-1 block w-3/4"
                                        placeholder="{{ __('رمز عبور') }}"
                                    />

                                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <x-secondary-button x-on:click="$dispatch('close')">
                                        {{ __('انصراف') }}
                                    </x-secondary-button>

                                    <x-danger-button class="mr-3">
                                        {{ __('حذف حساب کاربری') }}
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
