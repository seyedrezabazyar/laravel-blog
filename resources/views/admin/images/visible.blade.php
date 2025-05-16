<!DOCTYPE html>
<html>
<head>
    <title>تصاویر تأیید شده</title>
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

        .reset-btn {
            flex: 1;
            padding: 12px 0;
            text-align: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background-color: #3B82F6; /* آبی */
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
        <h1 class="text-2xl font-bold">تصاویر تأیید شده</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button" style="background-color: #3B82F6;">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button" style="background-color: #10B981;">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button" style="background-color: #EF4444;">رد شده</a>
            <a href="{{ route('admin.gallery.missing') }}" class="menu-button" style="background-color: #F59E0B;">گمشده</a>
            <a href="{{ route('admin.images.checker') }}" class="menu-button" style="background-color: #8B5CF6;">بررسی تصاویر گمشده</a>
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
                    <button onclick="resetImage({{ $image->id }})" class="reset-btn">بازگرداندن به حالت بررسی نشده</button>
                </div>
            </div>
        @empty
            <div class="w-full text-center py-8 bg-yellow-100 text-yellow-700">
                <p>هیچ تصویر تأیید شده‌ای یافت نشد.</p>
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                    showNotification('تصویر با موفقیت به حالت بررسی نشده بازگردانده شد', 'success');
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

        // حذف اعلان بعد از 3 ثانیه
        setTimeout(() => {
            notification.style.display = 'none';
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
