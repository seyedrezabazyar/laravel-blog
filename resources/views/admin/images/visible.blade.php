<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>گالری تصاویر - تصاویر تأیید شده</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="{{ asset('css/gallery.css') }}" rel="stylesheet">
</head>
<body>
<div>
    <div class="header">
        <h1 class="text-2xl font-bold">گالری تصاویر - تصاویر تأیید شده</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button bg-blue-500">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button active bg-green-500">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button bg-red-500">رد شده</a>
            <a href="{{ route('admin.gallery.missing') }}" class="menu-button bg-yellow-500">گمشده</a>
            <a href="{{ route('admin.images.checker') }}" class="menu-button bg-purple-500">بررسی تصاویر گمشده</a>
            <a href="{{ url('/dashboard') }}" class="menu-button bg-gray-500">بازگشت به داشبورد</a>
        </div>
    </div>

    <div id="notification" class="notification"></div>

    @if(session('success'))
        <div class="text-center py-3 bg-green-100 text-green-700 mb-4 font-bold">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="text-center py-3 bg-red-100 text-red-700 mb-4 font-bold">{{ session('error') }}</div>
    @endif

    <div class="image-grid" id="image-gallery">
        @forelse ($images as $image)
            <div class="image-item w-1/4 h-[25vw]" data-image-id="{{ $image->id }}">
                <div class="image-container" onclick="showFullscreen('{{ $image->image_url ?? asset('storage/' . $image->image_path) }}')">
                    <span class="status-badge approved-badge">تأیید شده</span>
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر" onerror="this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="image-details">
                    <div>شناسه: {{ $image->id }}</div>
                    <div class="truncate">{{ basename($image->image_path) }}</div>
                </div>
                <div class="button-container">
                    <button onclick="resetImage({{ $image->id }})" class="reset-btn">بازگرداندن</button>
                </div>
            </div>
        @empty
            <div class="w-full text-center py-8 bg-yellow-100 text-yellow-700">
                <p class="font-bold text-lg">هیچ تصویر تأیید شده‌ای یافت نشد.</p>
            </div>
        @endforelse
    </div>

    <div id="fullscreen-container" class="fullscreen" onclick="closeFullscreen()">
        <img id="fullscreen-image" src="" alt="تصویر تمام‌صفحه">
    </div>

    <div class="footer">
        <div class="text-center py-4">{{ $images->links() }}</div>
    </div>
</div>

<script src="{{ asset('js/gallery.js') }}"></script>
<script>
    const resetImage=e=>processImageAction(e,"{{ url('admin/gallery/reset') }}/"+e,"هیچ تصویر تأیید شده‌ای وجود ندارد.","تصویر به حالت بررسی نشده بازگردانده شد");
</script>
</body>
</html>
