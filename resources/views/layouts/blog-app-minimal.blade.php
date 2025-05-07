<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' | ' : '' }}کتابستان</title>

    <!-- Critical CSS - inline styles for faster initial render -->
    <style>
        /* Critical styles for above-the-fold content */
        @font-face {
            font-family: Vazirmatn;
            src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: Vazirmatn;
            src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn-Bold.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        body {
            font-family: 'Vazirmatn', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            direction: rtl;
            text-align: right;
        }
        .header {
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0.75rem 0;
        }
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .flex {
            display: flex;
        }
        .items-center {
            align-items: center;
        }
        .justify-between {
            justify-content: space-between;
        }
        .bg-white {
            background-color: #fff;
        }
        .bg-gray-50 {
            background-color: #f9fafb;
        }
        .text-gray-700 {
            color: #374151;
        }
        .text-gray-500 {
            color: #6b7280;
        }
        .text-indigo-600 {
            color: #4f46e5;
        }
        .font-bold {
            font-weight: 700;
        }
        .font-medium {
            font-weight: 500;
        }
        .text-xl {
            font-size: 1.25rem;
        }
        .hover\:text-indigo-600:hover {
            color: #4f46e5;
        }
        .transition {
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
        .shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        a {
            color: inherit;
            text-decoration: none;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        /* Grid system - minimal implementation */
        .grid {
            display: grid;
        }
        .gap-6 {
            gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .sm\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 768px) {
            .md\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .lg\:flex-row {
                flex-direction: row;
            }
            .lg\:grid-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .flex-col {
            flex-direction: column;
        }
    </style>

    <!-- Preload critical assets -->
    <link rel="preload" href="{{ asset('images/default-book.png') }}" as="image" fetchpriority="high">
    <link rel="preconnect" href="{{ config('app.custom_image_host', 'https://images.balyan.ir') }}" crossorigin>

    <!-- Defer non-critical CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ asset('css/app.css') }}"></noscript>

    <!-- Page-specific styles -->
    @stack('styles')
</head>
<body>
<!-- Simple header for faster loading -->
<header class="header">
    <div class="container flex justify-between items-center">
        <a href="{{ url('/') }}" class="text-xl font-bold text-indigo-600">کتابستان</a>
        <nav class="hidden md:flex space-x-6 space-x-reverse">
            <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-indigo-600 transition">وبلاگ</a>
            <a href="{{ route('blog.categories') }}" class="text-gray-700 hover:text-indigo-600 transition">دسته‌بندی‌ها</a>
            <a href="{{ route('blog.search') }}" class="text-gray-700 hover:text-indigo-600 transition">جستجو</a>
        </nav>
        <div class="md:hidden">
            <button type="button" id="mobile-menu-button" class="text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
</header>

<!-- Mobile menu - hidden by default -->
<div id="mobile-menu" class="hidden bg-white shadow-md pt-2 pb-4 absolute w-full z-50">
    <div class="container">
        <nav class="flex flex-col space-y-3">
            <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-indigo-600 py-2 transition">وبلاگ</a>
            <a href="{{ route('blog.categories') }}" class="text-gray-700 hover:text-indigo-600 py-2 transition">دسته‌بندی‌ها</a>
            <a href="{{ route('blog.search') }}" class="text-gray-700 hover:text-indigo-600 py-2 transition">جستجو</a>
        </nav>
    </div>
</div>

<!-- Main content -->
<main>
    @yield('content')
</main>

<!-- Simplified footer -->
<footer class="py-8 bg-gray-50 border-t border-gray-200 mt-12">
    <div class="container text-center">
        <p class="text-gray-500">© {{ date('Y') }} کتابخانه دیجیتال کتابستان | تمامی حقوق محفوظ است.</p>
    </div>
</footer>

<!-- Minimal JS with defer for faster page load -->
<script defer>
    // Simple mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Handle image errors
        document.querySelectorAll('img').forEach(function(img) {
            img.addEventListener('error', function() {
                if (!this.src.includes('default-book.png')) {
                    this.src = '{{ asset("images/default-book.png") }}';
                }
            });
        });
    });
</script>

<!-- Additional scripts -->
@stack('scripts')
</body>
</html>
