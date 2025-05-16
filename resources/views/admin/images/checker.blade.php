<!DOCTYPE html>
<html>
<head>
    <title>بررسی تصاویر گمشده</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* استایل‌های ساده برای راست چین کردن و رفع مشکلات RTL */
        .rtl-content {
            text-align: right;
            direction: rtl;
        }
        .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 0.25rem;
        }
        .progress-bar {
            height: 1.5rem;
            background-color: #4CAF50;
            border-radius: 0.25rem;
            width: 0%;
            transition: width 0.5s;
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4 rtl-content">
    <h1 class="text-2xl font-bold mb-4">بررسی تصاویر گمشده</h1>

    <div class="mb-4 flex flex-wrap">
        <a href="{{ route('admin.gallery') }}" class="bg-gray-500 text-white px-4 py-2 rounded ml-2 mb-2">گالری (بررسی نشده)</a>
        <a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded ml-2 mb-2">تصاویر تأیید شده</a>
        <a href="{{ route('admin.gallery.hidden') }}" class="bg-red-500 text-white px-4 py-2 rounded ml-2 mb-2">تصاویر رد شده</a>
        <a href="{{ route('admin.gallery.missing') }}" class="bg-yellow-500 text-white px-4 py-2 rounded ml-2 mb-2">تصاویر گمشده</a>
        <a href="{{ route('admin.images.checker') }}" class="bg-blue-500 text-white px-4 py-2 rounded ml-2 mb-2">بررسی تصاویر گمشده</a>
    </div>

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

    @if(session('errors') && count(session('errors')) > 0)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-4">
            <p class="font-bold mb-2">خطاهای ثبت شده:</p>
            <ul class="list-disc mr-5">
                @foreach(session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('processed_count') && session('missing_count'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            <p><strong>تعداد تصاویر بررسی شده:</strong> {{ session('processed_count') }}</p>
            <p><strong>تعداد تصاویر گمشده یافت شده:</strong> {{ session('missing_count') }}</p>
        </div>
    @endif

    <div class="bg-white p-6 rounded shadow-md mb-8">
        <h2 class="text-xl font-bold mb-4">بررسی تصاویر در محدوده آیدی مشخص</h2>

        <form action="{{ route('admin.images.check') }}" method="POST" id="checkForm">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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

            <div class="mb-4">
                <div class="text-gray-700 font-bold mb-2">پیش‌نمایش:</div>
                <p id="preview" class="text-gray-600">بررسی تصاویر از شناسه 1 تا 1000 (1000 تصویر) با اندازه دسته 50</p>
            </div>

            <button type="submit" id="submitBtn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                شروع بررسی تصاویر
            </button>
        </form>

        <div id="progress-section" class="mt-6 hidden">
            <h3 class="font-bold text-lg mb-2">در حال بررسی تصاویر...</h3>
            <div class="progress-container">
                <div id="progress-bar" class="progress-bar"></div>
            </div>
            <p id="progress-text" class="mt-2 text-center">0%</p>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">راهنمای استفاده</h2>
        <ul class="list-disc mr-5 space-y-2">
            <li><strong>شناسه شروع و پایان:</strong> محدوده آیدی تصاویری که می‌خواهید بررسی کنید را مشخص کنید.</li>
            <li><strong>اندازه دسته:</strong> این مقدار تعیین می‌کند در هر بررسی چند تصویر پردازش شود. مقادیر بزرگتر سرعت را افزایش می‌دهند اما ممکن است باعث خطای زمان اجرا شوند.</li>
            <li><strong>نکته مهم:</strong> بررسی تعداد زیادی تصویر ممکن است زمان‌بر باشد. برای بهترین نتیجه، محدوده‌های کوچک‌تر را بررسی کنید.</li>
            <li><strong>نتیجه:</strong> تصاویری که در دسترس نباشند یا کد وضعیت HTTP 200 نداشته باشند، به عنوان "گمشده" علامت‌گذاری می‌شوند.</li>
        </ul>
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

        previewElement.textContent = `بررسی تصاویر از شناسه ${startId} تا ${endId} (${totalImages} تصویر) با اندازه دسته ${batchSize}`;
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
            alert('شناسه شروع باید کوچکتر یا مساوی شناسه پایان باشد.');
            return;
        }

        // نمایش نوار پیشرفت
        submitBtn.disabled = true;
        progressSection.classList.remove('hidden');

        // شبیه‌سازی پیشرفت (چون نمی‌توانیم پیشرفت واقعی را ردیابی کنیم)
        simulateProgress(startId, endId);
    });

    function simulateProgress(startId, endId) {
        const totalImages = endId - startId + 1;
        const estimatedTimePerImage = 50; // میلی‌ثانیه
        const totalEstimatedTime = Math.min(totalImages * estimatedTimePerImage, 60000); // حداکثر 1 دقیقه

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

    // به‌روزرسانی اولیه پیش‌نمایش
    updatePreview();
</script>
</body>
</html>
