<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>بررسی تصاویر گمشده</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .card{background:#fff;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.1);margin-bottom:20px;padding:20px;transition:transform .3s ease,box-shadow .3s ease}.card:hover{box-shadow:0 8px 16px rgba(0,0,0,.1)}.progress-container{width:100%;height:20px;background:#e0e0e0;border-radius:.25rem;overflow:hidden;margin:10px 0}.progress-bar{height:100%;background:#10B981;border-radius:.25rem;width:0%;transition:width .5s ease}.submit-btn{background:#3B82F6;color:#fff;font-weight:700;padding:10px 16px;border:none;border-radius:4px;cursor:pointer;transition:background-color .3s ease,transform .2s ease}.submit-btn:hover{background:#2563EB;transform:translateY(-2px)}.notification{position:fixed;top:20px;right:20px;padding:15px 25px;color:#fff;border-radius:5px;z-index:9999;box-shadow:0 4px 15px rgba(0,0,0,.2);display:none;font-size:16px;font-weight:700;transition:opacity .3s ease,transform .3s ease;transform:translateY(-10px)}.animate-pulse{animation:pulse 2s cubic-bezier(.4,0,.6,1) infinite}@keyframes pulse{0%,100%{opacity:1}50%{opacity:.7}}
    </style>
</head>
<body>
<div>
    <div class="header">
        <h1 class="text-2xl font-bold">بررسی تصاویر گمشده</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button bg-blue-500">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button bg-green-500">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button bg-red-500">رد شده</a>
            <a href="{{ route('admin.gallery.missing') }}" class="menu-button bg-yellow-500">گمشده</a>
            <a href="{{ route('admin.images.checker') }}" class="menu-button active bg-purple-500">بررسی تصاویر گمشده</a>
            <a href="{{ url('/dashboard') }}" class="menu-button bg-gray-500">بازگشت به داشبورد</a>
        </div>
    </div>

    <div id="notification" class="notification"></div>

    <div class="container mx-auto p-4 mt-6">
        @if(session('success'))
            <div class="text-center py-3 bg-green-100 text-green-700 mb-6 rounded shadow font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="text-center py-3 bg-red-100 text-red-700 mb-6 rounded shadow font-bold">{{ session('error') }}</div>
        @endif
        @if(session('errors') && count(session('errors')) > 0)
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6">
                <p class="font-bold mb-2">خطاها:</p>
                <ul class="list-disc list-inside">
                    @foreach(session('errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('processed_count') && session('missing_count'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                <p class="font-bold mb-2">گزارش:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded shadow text-center">
                        <p class="text-2xl font-bold">{{ session('processed_count') }}</p>
                        <p class="text-gray-700">تصاویر بررسی شده</p>
                    </div>
                    <div class="bg-white p-4 rounded shadow text-center">
                        <p class="text-2xl font-bold">{{ session('missing_count') }}</p>
                        <p class="text-gray-700">تصاویر گمشده</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <h2 class="text-xl font-bold mb-4 border-b pb-2">بررسی تصاویر در محدوده آیدی</h2>
            <form action="{{ route('admin.images.check') }}" method="POST" id="checkForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="start_id" class="block text-gray-700 font-bold mb-2">شناسه شروع:</label>
                        <input type="number" id="start_id" name="start_id" value="{{ old('start_id', 1) }}" min="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('start_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_id" class="block text-gray-700 font-bold mb-2">شناسه پایان:</label>
                        <input type="number" id="end_id" name="end_id" value="{{ old('end_id', 1000) }}" min="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        @error('end_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="batch_size" class="block text-gray-700 font-bold mb-2">اندازه دسته:</label>
                        <input type="number" id="batch_size" name="batch_size" value="{{ old('batch_size', 50) }}" min="10" max="1000" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('batch_size')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="bg-gray-100 p-4 rounded-lg mb-6">
                    <div class="text-gray-700 font-bold mb-2">پیش‌نمایش:</div>
                    <p id="preview" class="text-gray-600">بررسی تصاویر از شناسه <span class="font-bold">1</span> تا <span class="font-bold">1000</span> (<span class="font-bold">1000</span> تصویر) با اندازه دسته <span class="font-bold">50</span></p>
                </div>
                <div id="progress-section" class="hidden mb-6">
                    <h3 class="font-bold text-lg mb-2">در حال بررسی...</h3>
                    <div class="progress-container">
                        <div id="progress-bar" class="progress-bar"></div>
                    </div>
                    <p id="progress-text" class="mt-2 text-center">0%</p>
                </div>
                <button type="submit" id="submitBtn" class="submit-btn">شروع بررسی</button>
            </form>
        </div>

        <div class="card">
            <h2 class="text-xl font-bold mb-4 border-b pb-2">راهنما</h2>
            <ul class="space-y-3 text-gray-700">
                <li><strong class="text-purple-700">شناسه شروع و پایان:</strong> محدوده آیدی تصاویر را مشخص کنید.</li>
                <li><strong class="text-purple-700">اندازه دسته:</strong> تعداد تصاویر در هر بررسی. مقادیر بزرگتر سریع‌تر اما ممکن است خطا ایجاد کند.</li>
                <li><strong class="text-red-600">نکته:</strong> برای بهترین نتیجه، محدوده‌های کوچک‌تر را بررسی کنید.</li>
                <li><strong class="text-blue-600">نتیجه:</strong> تصاویر در دسترس با کد HTTP 200 به عنوان گمشده علامت‌گذاری می‌شوند.</li>
            </ul>
        </div>
    </div>
</div>

<script src="{{ asset('js/gallery.js') }}"></script>
<script>
    const startIdInput=document.getElementById("start_id"),endIdInput=document.getElementById("end_id"),batchSizeInput=document.getElementById("batch_size"),previewElement=document.getElementById("preview"),checkForm=document.getElementById("checkForm"),submitBtn=document.getElementById("submitBtn"),progressSection=document.getElementById("progress-section"),progressBar=document.getElementById("progress-bar"),progressText=document.getElementById("progress-text");function updatePreview(){const e=parseInt(startIdInput.value)||1,t=parseInt(endIdInput.value)||1e3,n=parseInt(batchSizeInput.value)||50,o=t-e+1;previewElement.innerHTML=`بررسی تصاویر از شناسه <span class="font-bold">${e}</span> تا <span class="font-bold">${t}</span> (<span class="font-bold">${o}</span> تصویر) با اندازه دسته <span class="font-bold">${n}</span>`}startIdInput.addEventListener("input",updatePreview),endIdInput.addEventListener("input",updatePreview),batchSizeInput.addEventListener("input",updatePreview),checkForm.addEventListener("submit",e=>{const t=parseInt(startIdInput.value)||1,n=parseInt(endIdInput.value)||1e3;t>n&&(e.preventDefault(),showNotification("شناسه شروع باید کوچکتر یا مساوی شناسه پایان باشد.","error"));submitBtn.disabled=!0,submitBtn.innerHTML="در حال بررسی...",progressSection.classList.remove("hidden"),simulateProgress(t,n)});function simulateProgress(e,t){const n=t-e+1,o=50,a=Math.min(n*o,3e5),r=500,s=r/a*100;let i=0;const l=setInterval(()=>{i+=s,i>=100&&(clearInterval(l),i=99),progressBar.style.width=`${i}%`,progressText.textContent=`${Math.round(i)}%`},r)}updatePreview();
</script>
</body>
</html>
