<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('داشبورد') }}
            </h2>
            <a href="{{ route('blog.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 9h1v7a1 1 0 001 1h4a1 1 0 001-1v-4h2v4a1 1 0 001 1h4a1 1 0 001-1V9h1a1 1 0 00.707-1.707l-7-7z" />
                </svg>
                مشاهده سایت
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- آمار کلی -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- کل پست‌ها -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 transition hover:bg-indigo-50 h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">کل پست‌ها</h3>
                                <p class="text-3xl font-bold text-indigo-600">{{ \App\Models\Post::count() }}</p>
                            </div>
                            <div class="bg-indigo-100 rounded-full p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.posts.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center">
                                <span>مشاهده همه پست‌ها</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- پست‌های منتشر شده -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 transition hover:bg-green-50 h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">پست‌های منتشر شده</h3>
                                <p class="text-3xl font-bold text-green-600">{{ \App\Models\Post::where('is_published', true)->count() }}</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.posts.index') }}?filter=published" class="text-green-600 hover:text-green-800 text-sm flex items-center">
                                <span>مشاهده پست‌های منتشر شده</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- پیش‌نویس‌ها -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 transition hover:bg-yellow-50 h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">پیش‌نویس‌ها</h3>
                                <p class="text-3xl font-bold text-yellow-600">{{ \App\Models\Post::where('is_published', false)->count() }}</p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.posts.index') }}?filter=draft" class="text-yellow-600 hover:text-yellow-800 text-sm flex items-center">
                                <span>مشاهده پیش‌نویس‌ها</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- محتوای مخفی -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 transition hover:bg-red-50 h-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">محتوای مخفی</h3>
                                <p class="text-3xl font-bold text-red-600">{{ \App\Models\Post::where('hide_content', true)->count() }}</p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.posts.index') }}?filter=hidden" class="text-red-600 hover:text-red-800 text-sm flex items-center">
                                <span>مشاهده محتوای مخفی</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- آخرین پست‌های منتشر شده -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">آخرین پست‌های منتشر شده</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عنوان</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">دسته‌بندی</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نویسنده</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وضعیت</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاریخ</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(\App\Models\Post::with(['category', 'author'])->where('is_published', true)->latest()->take(5)->get() as $post)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($post->title, 40) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                {{ $post->category->name ?? 'بدون دسته‌بندی' }}
                                            </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $post->author->name ?? 'ناشناس' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($post->hide_content)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">مخفی</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">منتشر شده</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $post->created_at->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center">
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-indigo-600 hover:text-indigo-900 ml-3 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                ویرایش
                                            </a>
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-900 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                نمایش
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- کاربران و مدیران -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">کاربران و مدیران سیستم</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نام کاربر</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ایمیل</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نقش</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاریخ عضویت</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(\App\Models\User::all() as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $user->isAdmin() ? 'مدیر' : 'کاربر عادی' }}
                                            </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('profile.edit') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            ویرایش پروفایل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- بخش‌های اصلی مدیریت -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <!-- دسته‌بندی‌ها -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center bg-gradient-to-br from-indigo-50 to-purple-50 h-full">
                        <div class="mb-4">
                            <div class="bg-indigo-100 inline-flex p-3 rounded-full mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">دسته‌بندی‌ها</h3>
                        <p class="text-gray-600 mb-4">مدیریت دسته‌بندی‌های محتوا</p>
                        <div class="flex justify-between items-center">
                            <span class="text-indigo-600 font-semibold">{{ \App\Models\Category::count() }} دسته‌بندی</span>
                            <a href="{{ route('admin.categories.index') }}" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">
                                مشاهده
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ناشرها -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center bg-gradient-to-br from-blue-50 to-cyan-50 h-full">
                        <div class="mb-4">
                            <div class="bg-blue-100 inline-flex p-3 rounded-full mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">ناشرها</h3>
                        <p class="text-gray-600 mb-4">مدیریت ناشران کتاب‌ها</p>
                        <div class="flex justify-between items-center">
                            <span class="text-blue-600 font-semibold">{{ \App\Models\Publisher::count() }} ناشر</span>
                            <a href="{{ route('admin.publishers.index') }}" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
                                مشاهده
                            </a>
                        </div>
                    </div>
                </div>

                <!-- نویسنده‌ها -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center bg-gradient-to-br from-green-50 to-teal-50 h-full">
                        <div class="mb-4">
                            <div class="bg-green-100 inline-flex p-3 rounded-full mx-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">نویسنده‌ها</h3>
                        <p class="text-gray-600 mb-4">مدیریت نویسندگان کتاب‌ها</p>
                        <div class="flex justify-between items-center">
                            <span class="text-green-600 font-semibold">{{ \App\Models\Author::count() }} نویسنده</span>
                            <a href="{{ route('admin.authors.index') }}" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition">
                                مشاهده
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- مدیریت گالری تصاویر -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-6">مدیریت گالری تصاویر</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- همه تصاویر -->
                        <div class="border border-gray-200 rounded-lg p-6 bg-gradient-to-br from-gray-50 to-gray-100">
                            <div class="flex items-center justify-center mb-4">
                                <div class="bg-gray-200 rounded-full p-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-lg font-medium text-center mb-2">همه تصاویر</h4>
                            <div class="text-center mb-4">
                                <span class="text-2xl font-bold text-gray-700">
                                    {{ \App\Models\PostImage::count() }}
                                </span>
                                <span class="text-gray-500 text-sm block">تصویر</span>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('admin.gallery') }}" class="bg-gray-600 text-white py-2 px-6 rounded-md inline-block hover:bg-gray-700 transition">
                                    نمایش همه
                                </a>
                            </div>
                        </div>

                        <!-- تصاویر تایید شده -->
                        <div class="border border-gray-200 rounded-lg p-6 bg-gradient-to-br from-green-50 to-emerald-50">
                            <div class="flex items-center justify-center mb-4">
                                <div class="bg-green-100 rounded-full p-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-lg font-medium text-center mb-2">تصاویر تایید شده</h4>
                            <div class="text-center mb-4">
                                <span class="text-2xl font-bold text-green-600">
                                    {{ \App\Models\PostImage::where('hide_image', 'visible')->count() }}
                                </span>
                                <span class="text-gray-500 text-sm block">تصویر</span>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('admin.gallery.visible') }}" class="bg-green-600 text-white py-2 px-6 rounded-md inline-block hover:bg-green-700 transition">
                                    مشاهده
                                </a>
                            </div>
                        </div>

                        <!-- تصاویر رد شده -->
                        <div class="border border-gray-200 rounded-lg p-6 bg-gradient-to-br from-red-50 to-rose-50">
                            <div class="flex items-center justify-center mb-4">
                                <div class="bg-red-100 rounded-full p-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="text-lg font-medium text-center mb-2">تصاویر رد شده</h4>
                            <div class="text-center mb-4">
                                <span class="text-2xl font-bold text-red-600">
                                    {{ \App\Models\PostImage::where('hide_image', 'hidden')->count() }}
                                </span>
                                <span class="text-gray-500 text-sm block">تصویر</span>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('admin.gallery.hidden') }}" class="bg-red-600 text-white py-2 px-6 rounded-md inline-block hover:bg-red-700 transition">
                                    مشاهده
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
