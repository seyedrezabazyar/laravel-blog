<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>بررسی تصاویر گمشده</title>
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

        /* فرم و کارت‌ها */
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* نوار پیشرفت */
        .progress-container {
            width: 100%;
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 0.25rem;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-bar {
            height: 100%;
            background-color: #10B981;
            border-radius: 0.25rem;
            width: 0%;
            transition: width 0.5s ease;
        }

        /* دکمه‌ها */
        .submit-btn {
            background-color: #3B82F6;
            color: white;
            font-weight: bold;
            padding: 10px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .submit-btn:hover {
            background-color: #2563EB;
            transform: translateY(-2px);
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
    </style>
</head>
<body>
<div>
    <!-- هدر و منو -->
    <div class="header">
        <h1 class="text-2xl font-bold">بررسی تصاویر گمشده</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button" style="background-color: #3B82F6;">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button" style="background-color: #10B981;">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button" style="background-color: #EF4444;">رد شده</a>
            <a href="{{ route('admin.gallery.missing') }}" class="menu-button" style="background-color: #F59E0B;">گمشده</a>
            <a href="{{ route('admin.images.checker') }}" class="menu-button active" style="background-color: #8B5CF6;">بررسی تصاویر گمشده</a>
            <a href="{{ url('/dashboard') }}" class="menu-button" style="background-color: #6B7280;">بازگشت به داشبورد</a>
        </div>
    </div>

    <!-- اعلان -->
    <div id="notification" class="notification"></div>

    <!-- محتوای صفحه -->
    <div class="container mx-auto p-4 mt-6">
        <!-- پیام‌های سیستم -->
        @if(session('success'))
            <div class="text-center py-3 bg-green-100 text-green-700 mb-6 rounded shadow font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="text-center py-3 bg-red-100 text-red-700 mb-6 rounded shadow font-bold">
                {{ session('error') }}
            </div>
        @endif

        @if(session('errors') && count(session('errors')) > 0)
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6">
                <p class="font-bold mb-2">خطاهای ثبت شده:</p>
                <ul class="list-disc list-inside">
                    @foreach(session('errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('processed_count') && session('missing_count'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                <p class="font-bold mb-2">گزارش بررسی تصاویر:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded shadow">
                        <p class="text-center"><span class="text-2xl font-bold">{{ session('processed_count') }}</span></p>
                        <p class="text-center text-gray-700">تعداد تصاویر بررسی شده</p>
                    </div>
                    <div class="bg-white p-4 rounded shadow">
                        <p class="text-center"><span class="text-2xl font-bold">{{ session('missing_count') }}</span></p>
                        <p class="text-center text-gray-700">تعداد تصاویر گمشده یافت شده</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- فرم بررسی تصاویر -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4 border-b pb-2">بررسی تصاویر در محدوده آیدی مشخص</h2>

            <form action="{{ route('admin.images.check') }}" method="POST" id="checkForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="start_id" class="block text-gray-700 font-bold mb-2">شناسه شروع:</label>
                        <input type="number" id="start_id" name="start_id" value="{{ old('start_id', 1) }}" min="1"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('start_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_id" class="block text-gray-700 font-bold mb-2">شناسه پایان:</label>
                        <input type="number" id="end_id" name="end_id" value="{{ old('end_id', 1000) }}" min="1"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('end_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="batch_size" class="block text-gray-700 font-bold mb-2">اندازه دسته (تعداد در هر بررسی):</label>
                        <input type="number" id="batch_size" name="batch_size" value="{{ old('batch_size', 50) }}" min="10" max="1000"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('batch_size')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="bg-gray-100 p-4 rounded-lg mb-6">
                    <div class="text-gray-700 font-bold mb-2">پیش‌نمایش بررسی:</div>
                    <p id="preview" class="text-gray-600">بررسی تصاویر از شناسه <span class="font-bold">1</span> تا <span class="font-bold">1000</span> (<span class="font-bold">1000</span> تصویر) با اندازه دسته <span class="font-bold">50</span></p>
                </div>

                <div id="progress-section" class="hidden mb-6">
                    <h3 class="font-bold text-lg mb-2">در حال بررسی تصاویر...</h3>
                    <div class="progress-container">
                        <div id="progress-bar" class="progress-bar"></div>
                    </div>
                    <p id="progress-text" class="mt-2 text-center">0%</p>
                </div>

                <button type="submit" id="submitBtn" class="submit-btn">
                    شروع بررسی تصاویر
                </button>
            </form>
        </div>

        <!-- راهنمای استفاده -->
        <div class="card">
            <h2 class="text-xl font-bold mb-4 border-b pb-2">راهنمای استفاده</h2>
            <ul class="space-y-3 text-gray-700">
                <li><strong class="text-purple-700">شناسه شروع و پایان:</strong> محدوده آیدی تصاویری که می‌خواهید بررسی کنید را مشخص کنید.</li>
                <li><strong class="text-purple-700">اندازه دسته:</strong> این مقدار تعیین می‌کند در هر بررسی چند تصویر پردازش شود. مقادیر بزرگتر سرعت را افزایش می‌دهند اما ممکن است باعث خطای زمان اجرا شوند.</li>
                <li><strong class="text-red-600">نکته مهم:</strong> بررسی تعداد زیادی تصویر ممکن است زمان‌بر باشد. برای بهترین نتیجه، محدوده‌های کوچک‌تر را بررسی کنید.</li>
                <li><strong class="text-blue-600">نتیجه:</strong> تصاویری که در دسترس نباشند یا کد وضعیت HTTP 200 نداشته باشند، به عنوان "گمشده" علامت‌گذاری می‌شوند.</li>
            </ul>
        </div>
    </div>
</div>

<script>
    // به‌روزرسانی پیش‌نمایش با تغییر مقادیر
    const startIdInput = document.getElementById('start_id');
    const endIdInput = document.getElementById('end_id');
    const batchSizeInput = document.getElementById('batch_size');
    const previewElement = document.getElementById('preview');
    const checkForm = document.getElementById('checkForm');
    const submitBtn = document.getElementById('submitBtn');
    const progressSection = document.getElementById('progress-section');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');

    function updatePreview() {
        const startId = parseInt(startIdInput.value) || 1;
        const endId = parseInt(endIdInput.value) || 1000;
        const batchSize = parseInt(batchSizeInput.value) || 50;
        const totalImages = endId - startId + 1;

        previewElement.innerHTML = `بررسی تصاویر از شناسه <span class="font-bold">${startId}</span> تا <span class="font-bold">${endId}</span> (<span class="font-bold">${totalImages}</span> تصویر) با اندازه دسته <span class="font-bold">${batchSize}</span>`;
    }

    startIdInput.addEventListener('input', updatePreview);
    endIdInput.addEventListener('input', updatePreview);
    batchSizeInput.addEventListener('input', updatePreview);

    // نمایش نوار پیشرفت هنگام ارسال فرم
    checkForm.addEventListener('submit', function(e) {
        const startId = parseInt(startIdInput.value) || 1;
        const endId = parseInt(endIdInput.value) || 1000;

        // بررسی معتبر بودن ورودی‌ها
        if (startId > endId) {
            e.preventDefault();
            showNotification('شناسه شروع باید کوچکتر یا مساوی شناسه پایان باشد.', 'error');
            return;
        }

        // نمایش نوار پیشرفت
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'در حال بررسی...';
        progressSection.classList.remove('hidden');

        // شبیه‌سازی پیشرفت (چون نمی‌توانیم پیشرفت واقعی را ردیابی کنیم)
        simulateProgress(startId, endId);
    });

    function simulateProgress(startId, endId) {
        const totalImages = endId - startId + 1;
        const estimatedTimePerImage = 50; // میلی‌ثانیه
        const totalEstimatedTime = Math.min(totalImages * estimatedTimePerImage, 300000); // حداکثر 5 دقیقه

        let progress = 0;
        const interval = 500; // به‌روزرسانی هر 0.5 ثانیه
        const increment = (interval / totalEstimatedTime) * 100;

        const timer = setInterval(() => {
            progress += increment;
            if (progress >= 100) {
                clearInterval(timer);
                progress = 99; // نگه داشتن در 99% تا زمانی که پاسخ سرور دریافت شود
            }

            progressBar.style.width = `${progress}%`;
            progressText.textContent = `${Math.round(progress)}%`;
        }, interval);
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

    // به‌روزرسانی اولیه پیش‌نمایش
    updatePreview();
</script>
</body>
</html>
