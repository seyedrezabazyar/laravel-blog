<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' | ' : '' }}کتابستان - دنیای کتاب و کتابخوانی</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Vazirmatn', 'Figtree', sans-serif;
        }

        /* Hero section animation */
        .fade-in {
            animation: fadeIn 1.5s ease-in-out;
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Book hover effect */
        .book-card {
            transition: all 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Quote animation */
        .quote-animation {
            animation: fadeInUp 2s ease;
        }

        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* RTL styles - add these to fix RTL issues */
        .rtl, [dir=rtl] {
            direction: rtl;
            text-align: right;
        }

        .ltr, [dir=ltr] {
            direction: ltr;
            text-align: left;
        }

        .space-x-reverse > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }

        /* Fix for RTL margins */
        .ms-2 {
            margin-right: 0.5rem !important;
        }

        .ms-3 {
            margin-right: 0.75rem !important;
        }

        .ms-4 {
            margin-right: 1rem !important;
        }

        .me-2 {
            margin-left: 0.5rem !important;
        }

        .me-3 {
            margin-left: 0.75rem !important;
        }

        .me-4 {
            margin-left: 1rem !important;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">
<!-- Header -->
@include('partials.header')

<!-- Main Content -->
<main>
    @yield('content')
</main>

<!-- Footer -->
@include('partials.footer')

<!-- JavaScript for Mobile Menu Toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });

            // Close mobile menu when clicking on a menu item
            const mobileMenuItems = document.querySelectorAll('#mobile-menu a');
            mobileMenuItems.forEach(item => {
                item.addEventListener('click', function() {
                    mobileMenu.classList.add('hidden');
                });
            });
        }
    });
</script>

@stack('scripts')
</body>
</html>
