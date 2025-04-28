{{--<!DOCTYPE html>--}}
{{--<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">--}}
{{--<head>--}}
{{--    <meta charset="utf-8">--}}
{{--    <meta name="viewport" content="width=device-width, initial-scale=1">--}}
{{--    <meta name="csrf-token" content="{{ csrf_token() }}">--}}

{{--    <title>{{ isset($title) ? $title . ' | ' : '' }}{{ $post->title ?? 'کتابستان' }} - دنیای کتاب و کتابخوانی</title>--}}

{{--    <!-- Fonts -->--}}
{{--    <link rel="preconnect" href="https://fonts.googleapis.com">--}}
{{--    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">--}}

{{--    <!-- Styles / Scripts -->--}}
{{--    @vite(['resources/css/app.css', 'resources/js/app.js'])--}}

{{--    <style>--}}
{{--        body {--}}
{{--            font-family: 'Vazirmatn', 'Figtree', sans-serif;--}}
{{--        }--}}

{{--        /* Hero section animation */--}}
{{--        .fade-in {--}}
{{--            animation: fadeIn 1.5s ease-in-out;--}}
{{--        }--}}

{{--        @keyframes fadeIn {--}}
{{--            0% { opacity: 0; transform: translateY(20px); }--}}
{{--            100% { opacity: 1; transform: translateY(0); }--}}
{{--        }--}}

{{--        /* Book hover effect */--}}
{{--        .book-card {--}}
{{--            transition: all 0.3s ease;--}}
{{--        }--}}

{{--        .book-card:hover {--}}
{{--            transform: translateY(-10px);--}}
{{--            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);--}}
{{--        }--}}

{{--        /* Quote animation */--}}
{{--        .quote-animation {--}}
{{--            animation: fadeInUp 2s ease;--}}
{{--        }--}}

{{--        @keyframes fadeInUp {--}}
{{--            0% { opacity: 0; transform: translateY(30px); }--}}
{{--            100% { opacity: 1; transform: translateY(0); }--}}
{{--        }--}}

{{--        /* RTL styles - add these to fix RTL issues */--}}
{{--        .rtl, [dir=rtl] {--}}
{{--            direction: rtl;--}}
{{--            text-align: right;--}}
{{--        }--}}

{{--        .ltr, [dir=ltr] {--}}
{{--            direction: ltr;--}}
{{--            text-align: left;--}}
{{--        }--}}

{{--        .space-x-reverse > :not([hidden]) ~ :not([hidden]) {--}}
{{--            --tw-space-x-reverse: 1;--}}
{{--        }--}}

{{--        /* Fix for RTL margins */--}}
{{--        .ms-2 {--}}
{{--            margin-right: 0.5rem !important;--}}
{{--        }--}}

{{--        .ms-3 {--}}
{{--            margin-right: 0.75rem !important;--}}
{{--        }--}}

{{--        .ms-4 {--}}
{{--            margin-right: 1rem !important;--}}
{{--        }--}}

{{--        .me-2 {--}}
{{--            margin-left: 0.5rem !important;--}}
{{--        }--}}

{{--        .me-3 {--}}
{{--            margin-left: 0.75rem !important;--}}
{{--        }--}}

{{--        .me-4 {--}}
{{--            margin-left: 1rem !important;--}}
{{--        }--}}

{{--        /* Custom column widths */--}}
{{--        @media (min-width: 1024px) {--}}
{{--            .lg\:w-3\/10 {--}}
{{--                width: 30%;--}}
{{--            }--}}
{{--            .lg\:w-7\/10 {--}}
{{--                width: 70%;--}}
{{--            }--}}
{{--        }--}}
{{--    </style>--}}

{{--    @stack('styles')--}}
{{--</head>--}}
{{--<body class="font-sans antialiased bg-gray-50 text-gray-800">--}}
{{--<!-- Header -->--}}
{{--@include('partials.header')--}}

{{--<!-- Main Content -->--}}
{{--<main>--}}
{{--    <!-- نان‌برد و هدر کتاب -->--}}
{{--    <div class="bg-gray-50 py-6 mb-6 border-b border-gray-100">--}}
{{--        <div class="container mx-auto px-4">--}}
{{--            <div class="flex items-center text-sm mb-4">--}}
{{--                <a href="{{ route('blog.index') }}" class="text-blue-600 hover:text-blue-800 transition font-medium">خانه</a>--}}
{{--                <span class="mx-2 text-gray-400">›</span>--}}
{{--                <a href="{{ route('blog.category', $post->category->slug) }}" class="text-blue-600 hover:text-blue-800 transition font-medium">{{ $post->category->name }}</a>--}}
{{--                <span class="mx-2 text-gray-400">›</span>--}}
{{--                <span class="text-gray-600">{{ $post->title }}</span>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <div class="container mx-auto px-4 pb-12">--}}
{{--        <div class="flex flex-col lg:flex-row gap-8">--}}
{{--            <!-- ستون راست - تصویر و دکمه خرید (30%) -->--}}
{{--            <div class="w-full lg:w-3/10">--}}

{{--                <!-- تصویر کتاب -->--}}
{{--                <div class="card mb-6 overflow-hidden rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">--}}
{{--                    <div class="relative">--}}
{{--                        @if($post->featuredImage)--}}
{{--                            @if(auth()->check() && auth()->user()->isAdmin())--}}
{{--                                 مدیر سایت همیشه تصویر اصلی را می‌بیند--}}
{{--                                <div class="relative">--}}
{{--                                    <img src="{{ $post->featuredImage->image_url }}" alt="{{ $post->title }}" class="w-full h-auto">--}}

{{--                                     نمایش پیام مخفی بودن روی تصویر برای مدیران--}}
{{--                                    @if($post->featuredImage->hide_image)--}}
{{--                                        <div class="absolute inset-0 bg-red-500 bg-opacity-20 flex items-center justify-center">--}}
{{--                            <span class="bg-red-600 text-white px-4 py-2 rounded-md font-bold shadow-lg">--}}
{{--                                تصویر مخفی شده است--}}
{{--                            </span>--}}
{{--                                        </div>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
{{--                            @elseif(!$post->featuredImage->hide_image)--}}
{{--                                 کاربران عادی و مهمان‌ها فقط تصاویر غیر مخفی را می‌بینند--}}
{{--                                <img src="{{ $post->featuredImage->display_url }}" alt="{{ $post->title }}" class="w-full h-auto">--}}
{{--                            @else--}}
{{--                                 تصویر پیش‌فرض برای تصاویر مخفی (برای کاربران عادی و مهمان‌ها)--}}
{{--                                <div class="w-full h-64 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">--}}
{{--                                    <img src="{{ asset('images/default-book.png') }}" alt="{{ $post->title }}" class="max-h-40">--}}
{{--                                </div>--}}
{{--                            @endif--}}
{{--                        @else--}}
{{--                             تصویر پیش‌فرض برای پست‌های بدون تصویر--}}
{{--                            <div class="w-full h-64 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">--}}
{{--                                <img src="{{ asset('images/default-book.png') }}" alt="{{ $post->title }}" class="max-h-40">--}}
{{--                            </div>--}}
{{--                        @endif--}}

{{--                        @if($post->publication_year)--}}
{{--                            <div class="absolute top-4 right-4 bg-white px-3 py-1.5 rounded-md text-sm font-semibold text-gray-700 shadow-sm">--}}
{{--                                {{ $post->publication_year }}--}}
{{--                            </div>--}}
{{--                        @endif--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <!-- دکمه خرید کتاب -->--}}
{{--                @if($post->purchase_link)--}}
{{--                    <div class="mb-6">--}}
{{--                        <a href="{{ $post->purchase_link }}" target="_blank" class="btn btn-primary block text-center py-3 text-lg font-bold rounded-lg transition-all hover:shadow-lg bg-blue-600 hover:bg-blue-700 text-white">--}}
{{--                            خرید کتاب از سایت ناشر--}}
{{--                        </a>--}}
{{--                        <p class="text-xs text-gray-500 text-center mt-2">انتقال به وب‌سایت رسمی ناشر</p>--}}

{{--                        <!-- نمایش باکس وضعیت کشور -->--}}
{{--                        <div class="mt-3 py-2 px-3 rounded-lg text-center text-sm font-medium {{ $isIranianIp ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">--}}
{{--                            {{ $isIranianIp ? 'ایران' : 'خارج' }}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--            </div>--}}

{{--            <!-- ستون چپ - عنوان، اطلاعات و محتوای کتاب (70%) -->--}}
{{--            <div class="w-full lg:w-7/10">--}}
{{--                @yield('content')--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        @yield('related_posts')--}}
{{--    </div>--}}
{{--</main>--}}

{{--<!-- Footer -->--}}
{{--@include('partials.footer')--}}

{{--<!-- JavaScript for Mobile Menu Toggle -->--}}
{{--<script>--}}
{{--    document.addEventListener('DOMContentLoaded', function() {--}}
{{--        const mobileMenuButton = document.getElementById('mobile-menu-button');--}}
{{--        const mobileMenu = document.getElementById('mobile-menu');--}}

{{--        if (mobileMenuButton && mobileMenu) {--}}
{{--            mobileMenuButton.addEventListener('click', function() {--}}
{{--                mobileMenu.classList.toggle('hidden');--}}
{{--            });--}}

{{--            // Close mobile menu when clicking on a menu item--}}
{{--            const mobileMenuItems = document.querySelectorAll('#mobile-menu a');--}}
{{--            mobileMenuItems.forEach(item => {--}}
{{--                item.addEventListener('click', function() {--}}
{{--                    mobileMenu.classList.add('hidden');--}}
{{--                });--}}
{{--            });--}}
{{--        }--}}
{{--    });--}}
{{--</script>--}}

{{--@stack('scripts')--}}
{{--</body>--}}
{{--</html>--}}
