<!DOCTYPE html>
<html>
<head>
    <title>گالری تصاویر</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* استایل‌های ساده برای راست چین کردن و رفع مشکلات RTL */
        .rtl-content {
            text-align: right;
            direction: rtl;
        }

        /* استایل برای تصاویر - نمایش بزرگتر */
        .img-wrapper {
            width: 100%;
            height: auto;
            max-height: 600px;
            overflow: hidden;
            cursor: pointer;
            margin-bottom: 8px;
            text-align: center;
        }

        .img-wrapper img {
            max-width: 100%;
            max-height: 600px;
            object-fit: contain;
        }

        .expanded {
            max-height: none !important;
        }

        .expanded img {
            max-height: none !important;
        }

        /* استایل برای اعلان */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            color: white;
            border-radius: 5px;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4 rtl-content">
    <h1 class="text-3xl font-bold mb-4">گالری تصاویر (بررسی نشده)</h1>

    <div class="mb-4 flex flex-wrap">
        <a href="{{ route('admin.gallery') }}" class="bg-blue-500 text-white px-4 py-2 rounded ml-2 mb-2">بررسی نشده</a>
        <a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded ml-2 mb-2">تأیید شده</a>
        <a href="{{ route('admin.gallery.hidden') }}" class="bg-red-500 text-white px-4 py-2 rounded ml-2 mb-2">رد شده</a>
        <a href="{{ route('admin.gallery.missing') }}" class="bg-yellow-500 text-white px-4 py-2 rounded ml-2 mb-2">گمشده</a>
        <a href="{{ route('admin.images.checker') }}" class="bg-purple-500 text-white px-4 py-2 rounded ml-2 mb-2">بررسی تصاویر گمشده</a>
    </div>

    <div class="mb-4">
        <button onclick="bulkApprove()" class="bg-green-500 text-white px-4 py-2 rounded font-bold">تأیید گروهی همه تصاویر</button>
    </div>

    <div id="notification" class="notification"></div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="image-gallery">
        @forelse ($images as $image)
            <div class="bg-white rounded shadow-md overflow-hidden" data-image-id="{{ $image->id }}">
                <!-- نوار اطلاعات و دکمه‌ها -->
                <div class="bg-gray-200 px-4 py-2 flex justify-between items-center">
                    <div class="text-lg font-bold">شناسه: {{ $image->id }}</div>
                    <div class="flex space-x-2 space-x-reverse">
                        <button onclick="approveImage({{ $image->id }})" class="bg-green-500 hover:bg-green-600 text-white px-4 py-1 rounded">تأیید</button>
                        <button onclick="rejectImage({{ $image->id }})" class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded">رد</button>
                        <button onclick="markMissingImage({{ $image->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1 rounded">گمشده</button>
                    </div>
                </div>

                <!-- تصویر -->
                <div class="img-wrapper" onclick="toggleImageSize(this)">
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر"
                         onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';">
                </div>

                <!-- مسیر فایل -->
                <div class="px-4 py-2 border-t border-gray-200">
                    <p class="text-xs text-gray-600 truncate" title="{{ $image->image_path }}">
                        <span class="font-bold">مسیر: </span>{{ $image->image_path }}
                    </p>
                </div>
            </div>
        @empty
            <div class="col-span-2 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p>هیچ تصویری برای بررسی یافت نشد.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $images->links() }}
    </div>
</div>

<script>
    // تابع برای تغییر اندازه تصویر با کلیک
    function toggleImageSize(wrapper) {
        wrapper.classList.toggle('expanded');
        const img = wrapper.querySelector('img');
        img.classList.toggle('expanded');
    }

    // تابع برای تأیید تصویر
    function approveImage(imageId) {
        fetch('{{ url("admin/gallery/approve") }}/' + imageId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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
        fetch('{{ url("admin/gallery/reject") }}/' + imageId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                    showNotification('تصویر با موفقیت رد شد', 'success');
                }
            })
            .catch(error => {
                console.error(error);
                showNotification('خطا در رد تصویر', 'error');
            });
    }

    // تابع برای علامت‌گذاری تصویر به عنوان گمشده
    function markMissingImage(imageId) {
        fetch('{{ url("admin/gallery/mark-missing") }}/' + imageId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                    showNotification('تصویر با موفقیت به عنوان گمشده علامت‌گذاری شد', 'success');
                }
            })
            .catch(error => {
                console.error(error);
                showNotification('خطا در علامت‌گذاری تصویر', 'error');
            });
    }

    // تابع برای تأیید گروهی تصاویر
    function bulkApprove() {
        const imageIds = Array.from(document.querySelectorAll('#image-gallery [data-image-id]'))
            .map(element => element.getAttribute('data-image-id'));

        if (imageIds.length > 0) {
            if (confirm(`آیا از تایید همه ${imageIds.length} تصویر اطمینان دارید؟`)) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                imageIds.forEach(id => formData.append('image_ids[]', id));

                fetch('{{ url("admin/gallery/bulk-approve") }}', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // حذف همه تصاویر صفحه فعلی
                            document.querySelectorAll('#image-gallery [data-image-id]').forEach(element => element.remove());
                            showNotification('همه تصاویر با موفقیت تأیید شدند', 'success');
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        showNotification('خطا در تأیید گروهی تصاویر', 'error');
                    });
            }
        } else {
            showNotification('هیچ تصویری برای تأیید وجود ندارد', 'warning');
        }
    }

    // تابع برای نمایش اعلان‌ها
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;

        // تنظیم رنگ بر اساس نوع پیام
        notification.style.backgroundColor = type === 'success' ? '#10B981' :
            type === 'error' ? '#EF4444' : '#F59E0B';

        // نمایش اعلان
        notification.style.display = 'block';

        // حذف اعلان بعد از 3 ثانیه
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }

    // اضافه کردن کلیدهای میانبر برای سریع‌تر کردن کار
    document.addEventListener('keydown', function(e) {
        // فقط اگر تصویری در صفحه وجود داشته باشد
        const firstImage = document.querySelector('[data-image-id]');
        if (!firstImage) return;

        const imageId = firstImage.getAttribute('data-image-id');

        // A برای تأیید
        if (e.key === 'a' || e.key === 'A') {
            approveImage(imageId);
        }
        // R برای رد
        else if (e.key === 'r' || e.key === 'R') {
            rejectImage(imageId);
        }
        // M برای گمشده
        else if (e.key === 'm' || e.key === 'M') {
            markMissingImage(imageId);
        }
    });
</script>
</body>
</html>
