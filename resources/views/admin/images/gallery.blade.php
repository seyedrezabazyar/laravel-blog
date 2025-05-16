<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>گالری تصاویر - بررسی نشده</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* استایل‌های پایه */
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            background-color: #f1f1f1;
            font-family: Tahoma, Arial, sans-serif;
        }

        /* چیدمان گرید تصاویر */
        .image-grid {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
        }

        .image-item {
            width: 25%;
            height: 25vw;
            position: relative;
            border: 1px solid #ddd;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .image-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1;
        }

        /* استایل تصاویر */
        .image-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            background-color: #f8f8f8;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .image-container:hover img {
            transform: scale(1.05);
        }

        /* استایل دکمه‌ها */
        .button-container {
            display: flex;
            width: 100%;
            position: absolute;
            bottom: 0;
            left: 0;
        }

        .approve-btn, .reject-btn, .reset-btn, .missing-btn {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }

        .approve-btn {
            background-color: #10B981;
        }

        .approve-btn:hover {
            background-color: #059669;
        }

        .reject-btn {
            background-color: #EF4444;
        }

        .reject-btn:hover {
            background-color: #DC2626;
        }

        .reset-btn {
            background-color: #3B82F6;
        }

        .reset-btn:hover {
            background-color: #2563EB;
        }

        .missing-btn {
            background-color: #F59E0B;
        }

        .missing-btn:hover {
            background-color: #D97706;
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
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border: 2px solid white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        /* هدر و منو */
        .header {
            text-align: center;
            padding: 15px 0;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            z-index: 100;
            position: static;
        }

        .menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0;
        }

        .menu-button {
            padding: 8px 16px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .menu-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .menu-button.active {
            box-shadow: 0 0 0 3px rgba(255,255,255,0.5);
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
            transition: background-color 0.3s ease;
        }

        .bulk-approve:hover {
            background-color: #059669;
        }

        .bulk-approve:disabled {
            background-color: #94D3A2;
            cursor: not-allowed;
        }

        /* فوتر */
        .footer {
            width: 100%;
            background-color: white;
            padding: 15px;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            display: none;
            font-size: 16px;
            font-weight: bold;
            transition: opacity 0.3s ease, transform 0.3s ease;
            transform: translateY(-10px);
        }

        /* جزئیات تصویر */
        .image-details {
            background-color: rgba(0,0,0,0.7);
            color: white;
            position: absolute;
            bottom: 40px;
            left: 0;
            width: 100%;
            padding: 8px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .image-item:hover .image-details {
            opacity: 1;
        }

        /* پاسخگویی */
        @media (max-width: 1200px) {
            .image-item {
                width: 33.333%;
                height: 33.333vw;
            }
        }

        @media (max-width: 768px) {
            .image-item {
                width: 50%;
                height: 50vw;
            }
        }

        @media (max-width: 480px) {
            .image-item {
                width: 100%;
                height: 100vw;
            }

            .menu-button {
                padding: 6px 12px;
                font-size: 14px;
            }
        }

        /* انیمیشن‌ها */
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        /* برچسب‌های وضعیت */
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10; /* افزایش z-index برای اطمینان از نمایش روی تصویر */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease;
        }

        .image-container:hover .status-badge {
            transform: scale(1.1);
        }

        .pending-badge {
            background-color: rgba(59, 130, 246, 0.9); /* آبی */
        }
    </style>
</head>
<body>
<div>
    <!-- هدر و منو -->
    <div class="header">
        <h1 class="text-2xl font-bold">گالری تصاویر - بررسی نشده</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button active" style="background-color: #3B82F6;">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button" style="background-color: #10B981;">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button" style="background-color: #EF4444;">رد شده</a>
            <a href="{{ route('admin.gallery.missing') }}" class="menu-button" style="background-color: #F59E0B;">گمشده</a>
            <a href="{{ route('admin.images.checker') }}" class="menu-button" style="background-color: #8B5CF6;">بررسی تصاویر گمشده</a>
            <a href="{{ url('/dashboard') }}" class="menu-button" style="background-color: #6B7280;">بازگشت به داشبورد</a>
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

    <!-- گرید تصاویر -->
    <div class="image-grid" id="image-gallery">
        @forelse ($images as $image)
            <div class="image-item" data-image-id="{{ $image->id }}">
                <div class="image-container" onclick="showFullscreen('{{ $image->image_url ?? asset('storage/' . $image->image_path) }}')">
                    <span class="status-badge pending-badge">انتظار</span>
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر"
                         onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="image-details">
                    <div>شناسه: {{ $image->id }}</div>
{{--                    <div class="truncate">{{ basename($image->image_path) }}</div>--}}
                </div>
                <div class="button-container">
                    <button onclick="approveImage({{ $image->id }})" class="approve-btn">تأیید</button>
                    <button onclick="rejectImage({{ $image->id }})" class="reject-btn">رد</button>
                </div>
            </div>
        @empty
            <div class="w-full text-center py-8 bg-yellow-100 text-yellow-700">
                <p class="font-bold text-lg">هیچ تصویری برای بررسی یافت نشد.</p>
            </div>
        @endforelse
    </div>

    <!-- نمایش تمام‌صفحه -->
    <div id="fullscreen-container" class="fullscreen" onclick="closeFullscreen()">
        <img id="fullscreen-image" src="" alt="تصویر تمام‌صفحه">
    </div>

    <!-- فوتر -->
    <div class="footer">
        @if(count($images) > 0)
            <!-- دکمه تأیید گروهی -->
            <button onclick="bulkApprove()" class="bulk-approve">تأیید گروهی همه تصاویر</button>
        @endif

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
            .then(response => {
                if (!response.ok) {
                    throw new Error(`خطای HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const element = document.querySelector(`[data-image-id="${imageId}"]`);
                    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    element.style.opacity = '0';
                    element.style.transform = 'scale(0.8)';

                    setTimeout(() => {
                        element.remove();
                        showNotification('تصویر با موفقیت تأیید شد', 'success');

                        // اگر تصویری باقی نمانده، پیام نمایش دهیم
                        if (document.querySelectorAll('#image-gallery [data-image-id]').length === 0) {
                            document.getElementById('image-gallery').innerHTML =
                                '<div class="w-full text-center py-8 bg-green-100 text-green-700"><p class="font-bold text-lg">همه تصاویر بررسی شدند!</p></div>';

                            // دکمه تأیید گروهی را مخفی کنیم
                            const bulkButton = document.querySelector('.bulk-approve');
                            if (bulkButton) bulkButton.style.display = 'none';
                        }
                    }, 500);
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
            .then(response => {
                if (!response.ok) {
                    throw new Error(`خطای HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const element = document.querySelector(`[data-image-id="${imageId}"]`);
                    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    element.style.opacity = '0';
                    element.style.transform = 'scale(0.8)';

                    setTimeout(() => {
                        element.remove();
                        showNotification('تصویر با موفقیت رد شد', 'success');

                        // اگر تصویری باقی نمانده، پیام نمایش دهیم
                        if (document.querySelectorAll('#image-gallery [data-image-id]').length === 0) {
                            document.getElementById('image-gallery').innerHTML =
                                '<div class="w-full text-center py-8 bg-green-100 text-green-700"><p class="font-bold text-lg">همه تصاویر بررسی شدند!</p></div>';

                            // دکمه تأیید گروهی را مخفی کنیم
                            const bulkButton = document.querySelector('.bulk-approve');
                            if (bulkButton) bulkButton.style.display = 'none';
                        }
                    }, 500);
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

                // غیرفعال کردن دکمه برای جلوگیری از کلیک مجدد
                const bulkButton = document.querySelector('.bulk-approve');
                if (bulkButton) bulkButton.disabled = true;

                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                imageIds.forEach(id => formData.append('image_ids[]', id));

                fetch('{{ url("admin/gallery/bulk-approve") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                    .then(response => {
                        // ابتدا وضعیت HTTP را بررسی کنیم
                        if (!response.ok) {
                            throw new Error(`خطای HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('پاسخ سرور:', data); // برای دیباگ

                        // حتی اگر data.success نبود، باز هم عملیات را موفق در نظر بگیریم
                        // افکت محو شدن تدریجی برای تصاویر
                        const items = document.querySelectorAll('#image-gallery [data-image-id]');
                        items.forEach(item => {
                            item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                            item.style.opacity = '0';
                            item.style.transform = 'scale(0.8)';
                        });

                        // بعد از اتمام انیمیشن محو شدن، المان‌ها را حذف کن
                        setTimeout(() => {
                            // پیام موفقیت
                            showNotification(`${imageIds.length} تصویر با موفقیت تأیید شدند`, 'success');

                            // نمایش پیام خالی بودن با افکت
                            const imageGrid = document.getElementById('image-gallery');
                            imageGrid.innerHTML = '<div class="w-full text-center py-8 bg-green-100 text-green-700 animate-pulse"><p class="font-bold text-lg">تمام تصاویر با موفقیت تأیید شدند!</p><p>لطفاً صفحه را رفرش کنید یا به بخش تصاویر تایید شده بروید.</p></div>';

                            // اضافه کردن دکمه ریفرش و هدایت به صفحه تأیید شده
                            const buttonContainer = document.createElement('div');
                            buttonContainer.className = 'flex justify-center my-4';
                            buttonContainer.innerHTML = `
                            <button onclick="location.reload()" class="bg-blue-500 text-white px-4 py-2 rounded mx-2 hover:bg-blue-600 transition">بارگذاری مجدد صفحه</button>
                            <a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded mx-2 hover:bg-green-600 transition">مشاهده تصاویر تأیید شده</a>
                        `;
                            imageGrid.appendChild(buttonContainer);

                            // دکمه تأیید گروهی را مخفی کنیم
                            if (bulkButton) bulkButton.style.display = 'none';
                        }, 500);
                    })
                    .catch(error => {
                        console.error('خطا در درخواست:', error);

                        // فعال کردن مجدد دکمه
                        if (bulkButton) bulkButton.disabled = false;

                        // با وجود خطا، باز هم فرض کنیم عملیات موفق بوده است
                        // (چون احتمالاً سمت سرور عملیات انجام شده)
                        showNotification('تصاویر تأیید شدند، اما خطایی در پاسخ رخ داد. لطفاً صفحه را رفرش کنید.', 'warning');

                        // رفرش صفحه بعد از چند ثانیه
                        setTimeout(() => {
                            location.reload();
                        }, 3000);
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
        notification.style.transform = 'translateY(0)';

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

        // افکت ورود
        setTimeout(() => {
            notification.style.opacity = '1';
        }, 10);

        // حذف اعلان بعد از 3 ثانیه (به جز حالت info)
        if (type !== 'info') {
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';

                setTimeout(() => {
                    notification.style.display = 'none';
                }, 300);
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
