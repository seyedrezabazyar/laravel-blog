<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>@yield('title', 'مدیریت تصاویر')</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="{{ asset('css/gallery.css') }}" rel="stylesheet">
    @yield('styles')
</head>
<body>
<div>
    <!-- هدر و منو -->
    <div class="header">
        <h1 class="text-2xl font-bold">@yield('header-title')</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button @if(request()->routeIs('admin.gallery')) active @endif bg-blue-500">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button @if(request()->routeIs('admin.gallery.visible')) active @endif bg-green-500">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button @if(request()->routeIs('admin.gallery.hidden')) active @endif bg-red-500">رد شده</a>
            <a href="{{ route('admin.gallery.missing') }}" class="menu-button @if(request()->routeIs('admin.gallery.missing')) active @endif bg-yellow-500">گمشده</a>
            <a href="{{ route('admin.images.checker') }}" class="menu-button @if(request()->routeIs('admin.images.checker')) active @endif bg-purple-500">بررسی تصاویر گمشده</a>
            <a href="{{ url('/dashboard') }}" class="menu-button bg-gray-500">بازگشت به داشبورد</a>
        </div>
    </div>

    <!-- اعلان -->
    <div id="notification" class="notification"></div>

    <!-- پیام‌های سیستم -->
    @if(session('success'))
        <div class="text-center py-3 bg-green-100 text-green-700 mb-4 font-bold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="text-center py-3 bg-red-100 text-red-700 mb-4 font-bold">
            {{ session('error') }}
        </div>
    @endif

    <!-- محتوای اصلی -->
    @yield('content')

    <!-- نمایش تمام‌صفحه -->
    <div id="fullscreen-container" class="fullscreen" onclick="closeFullscreen()">
        <img id="fullscreen-image" src="" alt="تصویر تمام‌صفحه">
    </div>

    <!-- فوتر -->
    <div class="footer">
        @yield('footer')
    </div>
</div>

<script src="{{ asset('js/gallery.js') }}"></script>
@yield('scripts')
</body>
</html>
