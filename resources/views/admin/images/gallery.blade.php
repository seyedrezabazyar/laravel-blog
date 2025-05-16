<!DOCTYPE html>
<html>
<head>
    <title>گالری تصاویر</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* ریست کامل استایل‌ها */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            background-color: #f1f1f1;
        }

        .rtl-content {
            text-align: right;
            direction: rtl;
        }

        /* چیدمان و گرید تصاویر */
        .image-grid {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }

        .image-item {
            width: 25%; /* چهار تصویر در هر ردیف */
            height: 25vw; /* ارتفاع تصاویر بر اساس عرض صفحه تنظیم شود */
            position: relative;
            margin: 0; /* حذف فاصله‌های بین آیتم‌ها */
            padding: 0; /* حذف فاصله داخلی */
            border: 1px solid #ddd; /* حاشیه برای هر تصویر */
        }

        /* استایل تصاویر */
        .image-container {
            width: 100%;
            height: 100%; /* تصاویر کل ارتفاع ستون را پر کنند */
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            background-color: #f8f8f8;
        }

        .image-container img {
            width: 100%;
            height: 100%; /* تصاویر کل ارتفاع ستون را پر کنند */
            object-fit: cover; /* برش تصویر برای پر کردن کل ظرف */
        }

        /* استایل دکمه‌ها */
        .button-container {
            display: flex;
            width: 100%;
            position: absolute;
            bottom: 0;
            left: 0;
        }

        .approve-btn, .reject-btn {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .approve-btn {
            background-color: #10B981;
        }

        .reject-btn {
            background-color: #EF4444;
        }

        /* حالت تمام‌صفحه */
        .fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .fullscreen img {
            max-width: 98%;
            max-height: 98%;
            object-fit: contain;
        }

        /* هدر و منو */
        .header {
            text-align: center;
            padding: 10px 0;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
            z-index: 100;
            position: static;
        }

        .menu {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin: 5px 0;
        }

        .menu-button {
            padding: 5px 15px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* دکمه تأیید گروهی */
        .bulk-approve {
            width: 100%;
            padding: 15px;
            background-color: #10B981;
            color: white;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            border: none;
            font-size: 18px;
        }

        .bulk-approve:hover {
            background-color: #059669;
        }

        /* فوتر */
        .footer {
            width: 100%;
            background-color: white;
            padding: 15px;
            margin-top: 0;
            border-top: 1px solid #ddd;
        }

        /* اعلان */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            color: white;
            border-radius: 5px;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: none;
            font-size: 16px;
            font-weight: bold;
        }

        /* پاسخگویی */
        @media (max-width: 1200px) {
            .image-item {
                width: 33.333%; /* سه تصویر در هر ردیف */
                height: 33.333vw; /* ارتفاع تصاویر بر اساس عرض صفحه تنظیم شود */
            }
        }

        @media (max-width: 768px) {
            .image-item {
                width: 50%; /* دو تصویر در هر ردیف */
                height: 50vw; /* ارتفاع تصاویر بر اساس عرض صفحه تنظیم شود */
            }
        }

        @media (max-width: 480px) {
            .image-item {
                width: 100%; /* یک تصویر در هر ردیف */
                height: 100vw; /* ارتفاع تصاویر بر اساس عرض صفحه تنظیم شود */
            }
        }
    </style>
</head>
<body>
<div class="rtl-content">
    <!-- هدر و منو -->
    <div class="header">
        <h1 class="text-2xl font-bold">گالری تصاویر (بررسی نشده)</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button" style="background-color: #3B82F6;">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button" style="background-color: #10B981;">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button" style="background-color: #EF4444;">رد شده</a>
        </div>
    </div>

    <!-- اعلان -->
    <div id="notification" class="notification"></div>

    <!-- پیام‌های سیستم -->
    @if(session('success'))
        <div class="text-center py-2 bg-green-100 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="text-center py-2 bg-red-100 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- گرید تصاویر -->
    <div class="image-grid" id="image-gallery">
        @forelse ($images as $image)
            <div class="image-item" data-image-id="{{ $image->id }}">
                <div class="image-container" onclick="showFullscreen('{{ $image->image_url ?? asset('storage/' . $image->image_path) }}')">
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر"
                         onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="button-container">
                    <button onclick="approveImage({{ $image->id }})" class="approve-btn">تأیید</button>
                    <button onclick="rejectImage({{ $image->id }})" class="reject-btn">رد</button>
                </div>
            </div>
        @empty
            <div class="w-full text-center py-8 bg-yellow-100 text-yellow-700">
                <p>هیچ تصویری برای بررسی یافت نشد.</p>
            </div>
        @endforelse
    </div>

    <!-- نمایش تمام‌صفحه -->
    <div id="fullscreen-container" class="fullscreen" onclick="closeFullscreen()">
        <img id="fullscreen-image" src="" alt="تصویر تمام‌صفحه">
    </div>

    <!-- فوتر -->
    <div class="footer">
        <!-- دکمه تأیید گروهی -->
        <button onclick="bulkApprove()" class="bulk-approve">تأیید گروهی همه تصاویر</button>

        <!-- پیجینیشن -->
        <div class="text-center py-4">
            {{ $images->links() }}
        </div>
    </div>
</div>

<script>
    // نمایش تصویر در حالت تمام‌صفحه
    function showFullscreen(imageSrc) {
        const fullscreenContainer = document.getElementById('fullscreen-container');
        const fullscreenImage = document.getElementById('fullscreen-image');

        fullscreenImage.src = imageSrc;
        fullscreenContainer.style.display = 'flex';

        // جلوگیری از اسکرول صفحه
        document.body.style.overflow = 'hidden';
    }

    // بستن حالت تمام‌صفحه
    function closeFullscreen() {
        document.getElementById('fullscreen-container').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // تأیید تصویر
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

    // رد تصویر
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

    // تأیید گروهی تصاویر
    function bulkApprove() {
        const imageIds = Array.from(document.querySelectorAll('#image-gallery [data-image-id]'))
            .map(element => element.getAttribute('data-image-id'));

        if (imageIds.length > 0) {
            if (confirm(`آیا از تایید همه ${imageIds.length} تصویر اطمینان دارید؟`)) {
                // نمایش وضعیت در حال انجام
                showNotification('در حال تأیید تصاویر...', 'info');

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
                            // افکت محو شدن تدریجی برای تصاویر
                            const items = document.querySelectorAll('#image-gallery [data-image-id]');
                            items.forEach(item => {
                                item.style.transition = 'opacity 0.5s ease';
                                item.style.opacity = '0';
                            });

                            // بعد از اتمام انیمیشن محو شدن، المان‌ها را حذف کن
                            setTimeout(() => {
                                // پیام موفقیت
                                showNotification(`${imageIds.length} تصویر با موفقیت تأیید شدند`, 'success');

                                // نمایش پیام خالی بودن با افکت
                                const imageGrid = document.getElementById('image-gallery');
                                imageGrid.innerHTML = '<div class="w-full text-center py-8 bg-green-100 text-green-700 animate-pulse"><p>تمام تصاویر با موفقیت تأیید شدند!</p><p>لطفاً صفحه را رفرش کنید یا به بخش تصاویر تایید شده بروید.</p></div>';

                                // اضافه کردن دکمه ریفرش و هدایت به صفحه تأیید شده
                                const buttonContainer = document.createElement('div');
                                buttonContainer.className = 'flex justify-center my-4';
                                buttonContainer.innerHTML = `
                                <button onclick="location.reload()" class="bg-blue-500 text-white px-4 py-2 rounded mx-2">بارگذاری مجدد صفحه</button>
                                <a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded mx-2">مشاهده تصاویر تأیید شده</a>
                            `;
                                imageGrid.appendChild(buttonContainer);
                            }, 500);
                        } else {
                            showNotification('خطا در تأیید گروهی تصاویر', 'error');
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

    // نمایش اعلان‌ها
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        notification.textContent = message;

        // تنظیم رنگ بر اساس نوع پیام
        if (type === 'success') {
            notification.style.backgroundColor = '#10B981';
        } else if (type === 'error') {
            notification.style.backgroundColor = '#EF4444';
        } else if (type === 'warning') {
            notification.style.backgroundColor = '#F59E0B';
        } else if (type === 'info') {
            notification.style.backgroundColor = '#3B82F6';
        }

        // نمایش اعلان
        notification.style.display = 'block';

        // حذف اعلان بعد از 3 ثانیه (به جز حالت info)
        if (type !== 'info') {
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
    }

    // کلیدهای میانبر
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
        // ESC برای بستن حالت تمام‌صفحه
        else if (e.key === 'Escape' && document.getElementById('fullscreen-container').style.display === 'flex') {
            closeFullscreen();
        }
    });
</script>
</body>
</html>
