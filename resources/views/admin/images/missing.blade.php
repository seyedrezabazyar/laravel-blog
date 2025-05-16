<!DOCTYPE html>
<html>
<head>
    <title>تصاویر گمشده</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
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
    <h1 class="text-2xl font-bold mb-4">تصاویر گمشده</h1>

    <div class="mb-4 flex flex-wrap">
        <a href="{{ route('admin.gallery') }}" class="bg-gray-500 text-white px-4 py-2 rounded ml-2 mb-2">بررسی نشده</a>
        <a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded ml-2 mb-2">تأیید شده</a>
        <a href="{{ route('admin.gallery.hidden') }}" class="bg-red-500 text-white px-4 py-2 rounded ml-2 mb-2">رد شده</a>
        <a href="{{ route('admin.gallery.missing') }}" class="bg-yellow-500 text-white px-4 py-2 rounded ml-2 mb-2">گمشده</a>
        <a href="{{ route('admin.images.checker') }}" class="bg-purple-500 text-white px-4 py-2 rounded ml-2 mb-2">بررسی تصاویر گمشده</a>
    </div>

    <div id="notification" class="hidden fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50"></div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse ($images as $image)
            <div class="bg-white p-4 rounded shadow" data-image-id="{{ $image->id }}">
                <div class="img-wrapper mb-2">
                    <img src="{{ asset('images/default-book.png') }}" alt="تصویر گمشده">
                </div>
                <div class="mt-2">
                    <p class="mb-1">شناسه: {{ $image->id }}</p>
                    <p class="mb-1 text-xs overflow-hidden whitespace-nowrap overflow-ellipsis">
                        <span class="font-bold">مسیر: </span>{{ Str::limit($image->image_path, 30) }}
                    </p>
                    <p class="mb-1">وضعیت: {{ $image->hide_image }}</p>
                    <div class="flex mt-2">
                        <button onclick="resetImage({{ $image->id }})" class="bg-gray-500 text-white px-2 py-1 rounded text-sm">بازگرداندن به بررسی نشده</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p>هیچ تصویر گمشده‌ای یافت نشد.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $images->links() }}
    </div>
</div>

<script>
    // تنظیم CSRF token برای درخواست‌های AJAX
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // تابع برای بازگرداندن تصویر به حالت بررسی نشده
    function resetImage(imageId) {
        axios.post('{{ url("admin/gallery/reset") }}/' + imageId)
            .then(response => {
                if (response.data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                    showNotification('تصویر با موفقیت به حالت بررسی نشده بازگردانده شد', 'success');
                }
            })
            .catch(error => {
                console.error(error);
                showNotification('خطا در بازگرداندن تصویر', 'error');
            });
    }

    // تابع برای نمایش اعلان‌ها
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');

        // تنظیم کلاس‌ها و متن
        notification.textContent = message;
        notification.classList.remove('hidden', 'bg-green-500', 'bg-red-500', 'bg-yellow-500');

        if (type === 'success') {
            notification.classList.add('bg-green-500', 'text-white');
        } else if (type === 'error') {
            notification.classList.add('bg-red-500', 'text-white');
        } else if (type === 'warning') {
            notification.classList.add('bg-yellow-500', 'text-white');
        }

        // نمایش اعلان
        notification.classList.remove('hidden');

        // حذف اعلان بعد از 3 ثانیه
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
</script>
</body>
</html>
