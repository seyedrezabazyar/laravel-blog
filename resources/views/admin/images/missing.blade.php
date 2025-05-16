<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>گالری تصاویر - تصاویر گمشده</title>
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
            position: relative;
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

        /* برچسب تصویر گمشده */
        .missing-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(245, 158, 11, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 5;
        }

        /* استایل دکمه‌ها */
        .button-container {
            display: flex;
            width: 100%;
            position: absolute;
            bottom: 0;
            left: 0;
        }

        .reset-btn {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background-color: #3B82F6;
            transition: background-color 0.3s ease;
        }

        .reset-btn:hover {
            background-color: #2563EB;
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
    </style>
</head>
<body>
<div>
    <!-- هدر و منو -->
    <div class="header">
        <h1 class="text-2xl font-bold">گالری تصاویر - تصاویر گمشده</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button" style="background-color: #3B82F6;">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button" style="background-color: #10B981;">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button" style="background-color: #EF4444;">رد شده</a>
            <a href="{{ route('admin.gallery.missing') }}" class="menu-button active" style="background-color: #F59E0B;">گمشده</a>
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
                    <span class="missing-badge">گمشده</span>
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر"
                         onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="image-details">
                    <div>شناسه: {{ $image->id }}</div>
                    <div class="truncate">{{ basename($image->image_path ?? 'تصویر بدون مسیر') }}</div>
                </div>
                <div class="button-container">
                    <button onclick="resetImage({{ $image->id }})" class="reset-btn">بازگرداندن به حالت بررسی نشده</button>
                </div>
            </div>
        @empty
            <div class="w-full text-center py-8 bg-yellow-100 text-yellow-700">
                <p class="font-bold text-lg">هیچ تصویر گمشده‌ای یافت نشد.</p>
            </div>
        @endforelse
    </div>

    <!-- نمایش تمام‌صفحه -->
    <div id="fullscreen-container" class="fullscreen" onclick="closeFullscreen()">
        <img id="fullscreen-image" src="" alt="تصویر تمام‌صفحه">
    </div>

    <!-- فوتر -->
    <div class="footer">
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

    // بازگرداندن تصویر به حالت بررسی نشده
    function resetImage(imageId) {
        fetch('{{ url("admin/gallery/reset") }}/' + imageId, {
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
                        showNotification('تصویر با موفقیت به حالت بررسی نشده بازگردانده شد', 'success');

                        // اگر تصویری باقی نمانده، پیام نمایش دهیم
                        if (document.querySelectorAll('#image-gallery [data-image-id]').length === 0) {
                            document.getElementById('image-gallery').innerHTML =
                                '<div class="w-full text-center py-8 bg-yellow-100 text-yellow-700"><p class="font-bold text-lg">هیچ تصویر گمشده‌ای وجود ندارد.</p></div>';
                        }
                    }, 500);
                }
            })
            .catch(error => {
                console.error(error);
                showNotification('خطا در بازگرداندن تصویر', 'error');
            });
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

        // حذف اعلان بعد از 3 ثانیه
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 3000);
    }

    // کلیدهای میانبر
    document.addEventListener('keydown', function(e) {
        // ESC برای بستن حالت تمام‌صفحه
        if (e.key === 'Escape' && document.getElementById('fullscreen-container').style.display === 'flex') {
            closeFullscreen();
        }
    });
</script>
</body>
</html>
