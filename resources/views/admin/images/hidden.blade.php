<!DOCTYPE html>
<html>
<head>
    <title>تصاویر رد شده</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100" dir="rtl">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">تصاویر رد شده</h1>

    <div class="mb-4 flex space-x-4">
        <a href="{{ route('admin.gallery') }}" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">همه تصاویر</a>
        <a href="{{ route('admin.gallery.real') }}" class="bg-blue-500 text-white px-4 py-2 rounded ml-2">تصاویر واقعی</a>
        <a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded ml-2">تصاویر تأیید شده</a>
        <a href="{{ route('admin.gallery.hidden') }}" class="bg-red-500 text-white px-4 py-2 rounded ml-2">تصاویر رد شده</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse ($images as $image)
            <div class="bg-white p-4 rounded shadow">
                <div class="relative pb-[56.25%] h-0 overflow-hidden">
                    <img src="{{ $image->image_path }}" alt="تصویر" class="absolute top-0 left-0 w-full h-full object-contain"
                         onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="mt-2">
                    <p class="mb-1">شناسه: {{ $image->id }}</p>
                    <p class="mb-1 truncate"><span class="font-bold">مسیر:</span> {{ $image->image_path }}</p>
                    <p class="mb-1">وضعیت: {{ $image->hide_image }}</p>
                    <div class="flex mt-2">
                        <form action="{{ route('admin.gallery.approve', $image->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded text-sm">تغییر به تأیید شده</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p>هیچ تصویر رد شده‌ای یافت نشد.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $images->links() }}
    </div>
</div>
</body>
</html>
