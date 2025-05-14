<!DOCTYPE html>
<html>
<head>
    <title>تصاویر واقعی</title>
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
    <h1 class="text-2xl font-bold mb-4">تصاویر واقعی (کد 200)</h1>

    <div class="mb-4 flex flex-wrap">
        <a href="{{ route('admin.gallery') }}" class="bg-gray-500 text-white px-4 py-2 rounded ml-2 mb-2">همه تصاویر</a>
        <a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded ml-2 mb-2">تصاویر تأیید شده</a>
        <a href="{{ route('admin.gallery.hidden') }}" class="bg-red-500 text-white px-4 py-2 rounded ml-2 mb-2">تصاویر رد شده</a>
        <button onclick="bulkApprove()" class="bg-blue-500 text-white px-4 py-2 rounded mb-2">تأیید همه</button>
    </div>

    <div id="notification" class="hidden fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50"></div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4" id="image-gallery">
        @forelse ($validImages as $image)
            <div class="bg-white p-4 rounded shadow" data-image-id="{{ $image->id }}">
                <div class="img-wrapper mb-2">
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر"
                         onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="mt-2">
                    <p class="mb-1 text-sm">شناسه: {{ $image->id }}</p>
                    <p class="mb-1 text-xs overflow-hidden whitespace-nowrap overflow-ellipsis">
                        مسیر: {{ Str::limit($image->image_path, 30) }}
                    </p>
                    <div class="mt-2 flex justify-between">
                        <button onclick="approveImage({{ $image->id }})" class="bg-green-500 text-white px-2 py-1 rounded text-sm">تأیید</button>
                        <button onclick="rejectImage({{ $image->id }})" class="bg-red-500 text-white px-2 py-1 rounded text-sm">رد</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p>هیچ تصویر واقعی یافت نشد.</p>
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

    // تابع برای تأیید تصویر
    function approveImage(imageId) {
        axios.post('{{ url("admin/gallery/approve") }}/' + imageId)
            .then(response => {
                if (response.data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                    showNotification('تصویر با موفقیت تأیید شد', 'success');
                }
            })
            .catch(error => {
                console.error(error);
                showNotification('خطا در تأیید تصویر', 'error');
            });
    }

    // تابع برای رد تصویر
    function rejectImage(imageId) {
        axios.post('{{ url("admin/gallery/reject") }}/' + imageId)
            .then(response => {
                if (response.data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                    showNotification('تصویر با موفقیت رد شد', 'success');
                }
            })
            .catch(error => {
                console.error(error);
                showNotification('خطا در رد تصویر', 'error');
            });
    }

    // تابع برای تأیید گروهی تصاویر
    function bulkApprove() {
        const imageIds = Array.from(document.querySelectorAll('#image-gallery [data-image-id]'))
            .map(element => element.getAttribute('data-image-id'));

        if (imageIds.length > 0) {
            axios.post('{{ url("admin/gallery/bulk-approve") }}', { image_ids: imageIds })
                .then(response => {
                    if (response.data.success) {
                        // حذف همه تصاویر صفحه فعلی
                        document.querySelectorAll('#image-gallery [data-image-id]').forEach(element => element.remove());
                        showNotification('همه تصاویر با موفقیت تأیید شدند', 'success');
                    }
                })
                .catch(error => {
                    console.error(error);
                    showNotification('خطا در تأیید گروهی تصاویر', 'error');
                });
        } else {
            showNotification('هیچ تصویری برای تأیید وجود ندارد', 'warning');
        }
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
