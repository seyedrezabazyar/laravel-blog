<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' | ' : '' }}کتابستان</title>

    <!-- پیش‌بارگذاری منابع ضروری -->
    <link rel="preload" href="{{ asset('images/default-book.png') }}" as="image">
    <link rel="preconnect" href="{{ config('app.url') }}">
    <link rel="preconnect" href="{{ config('app.custom_image_host', 'https://images.balyan.ir') }}" crossorigin>

    <!-- فونت وزیرمتن - بهینه‌شده -->
    <style>
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
        }
        .header {
            background-color: #fff;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .hero-section {
            background: linear-gradient(to right, #eef2ff, #f5f3ff);
            padding: 5rem 1rem;
        }
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .fade-in {
            opacity: 1;
            transition: opacity .6s ease-in-out;
        }
        .search-container {
            position: relative;
            width: 100%;
        }
        @media(min-width:768px) {
            .md\:flex { display: flex; }
            .md\:w-1\/2 { width: 50%; }
        }
    </style>

    <!-- استایل‌های اصلی -->
    @vite(['resources/css/app.css'])

    <!-- استایل‌های اضافی -->
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
<!-- هدر سبک برای بارگذاری سریع‌تر -->
@includeFirst(['partials.header-minimal', 'partials.header'])

<!-- محتوای اصلی -->
<main>
    @yield('content')
</main>

<!-- بارگذاری تنبل فوتر -->
<div id="footer-container">
    @include('partials.footer')
</div>

<!-- اسکریپت‌ها -->
<script>
    // اسکریپت برای لیزی‌لود تصاویر
    document.addEventListener('DOMContentLoaded', function() {
        const lazyloadImages = document.querySelectorAll("img.lazyload");

        if ('IntersectionObserver' in window) {
            let imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let image = entry.target;
                        if (image.dataset.src) {
                            image.src = image.dataset.src;
                            image.classList.remove("lazyload");
                            imageObserver.unobserve(image);
                        }
                    }
                });
            });

            lazyloadImages.forEach(function(image) {
                imageObserver.observe(image);
            });
        } else {
            // برای مرورگرهای قدیمی که از IntersectionObserver پشتیبانی نمی‌کنند
            let lazyloadThrottleTimeout;

            function lazyload() {
                if(lazyloadThrottleTimeout) {
                    clearTimeout(lazyloadThrottleTimeout);
                }

                lazyloadThrottleTimeout = setTimeout(function() {
                    let scrollTop = window.pageYOffset;
                    lazyloadImages.forEach(function(img) {
                        if(img.offsetTop < (window.innerHeight + scrollTop)) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazyload');
                        }
                    });
                    if(lazyloadImages.length == 0) {
                        document.removeEventListener("scroll", lazyload);
                        window.removeEventListener("resize", lazyload);
                        window.removeEventListener("orientationChange", lazyload);
                    }
                }, 20);
            }

            document.addEventListener("scroll", lazyload);
            window.addEventListener("resize", lazyload);
            window.addEventListener("orientationChange", lazyload);
        }
    });
</script>
@stack('scripts')
</body>
</html>
