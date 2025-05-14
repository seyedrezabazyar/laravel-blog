<!DOCTYPE html>
<html>
<head>
    <title>تصاویر تأیید شده</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* استایل‌های ساده برای راست چین کردن و رفع مشکلات RTL */
        .rtl-content {
            text-align: right;
            direction: rtl;
        }
        .img-wrapper {
            height: 150px;
            overflow: hidden;
        }
        .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4 rtl-content">
    <h1 class="text-2xl font-bold mb-4">تصاویر تأیید شده</h1>

    <div class="mb-4 flex flex-wrap">
        <a href="{{ route('admin.gallery') }}" class="bg-gray-500 text-white px-4 py-2 rounded ml-2 mb-2">همه تصاویر</a>
        <a href="{{ route('admin.gallery.real') }}" class="bg-blue-500 text-white px-4 py-2 rounded ml-2 mb-2">تصاویر واقعی</a>
        <a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded ml-2 mb-2">تصاویر تأیید شده</a>
        <a href="{{ route('admin.gallery.hidden') }}" class="bg-red-500 text-white px-4 py-2 rounded ml-2 mb-2">تصاویر رد شده</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse ($images as $image)
            <div class="bg-white p-4 rounded shadow">
                <div class="img-wrapper mb-2">
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر"
                         onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="mt-2">
                    <p class="mb-1">شناسه: {{ $image->id }}</p>
                    <p class="mb-1 text-xs overflow-hidden whitespace-nowrap overflow-ellipsis">
                        <span class="font-bold">مسیر: </span>{{ Str::limit($image->image_path, 30) }}
                    </p>
                    <p class="mb-1">وضعیت: {{ $image->hide_image }}</p>
                    <div class="flex mt-2">
                        <form action="{{ route('admin.gallery.reject', $image->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded text-sm">تغییر به رد شده</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p>هیچ تصویر تأیید شده‌ای یافت نشد.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $images->links() }}
    </div>
</div>
</body>
</html>
