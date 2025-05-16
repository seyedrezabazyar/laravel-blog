<!DOCTYPE html>
<html dir="rtl">
<head>
    <title>گالری تصاویر - بررسی نشده</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="{{ asset('css/gallery.css') }}" rel="stylesheet">
</head>
<body>
<div>
    <div class="header">
        <h1 class="text-2xl font-bold">گالری تصاویر - بررسی نشده</h1>
        <div class="menu">
            <a href="{{ route('admin.gallery') }}" class="menu-button active bg-blue-500">بررسی نشده</a>
            <a href="{{ route('admin.gallery.visible') }}" class="menu-button bg-green-500">تأیید شده</a>
            <a href="{{ route('admin.gallery.hidden') }}" class="menu-button bg-red-500">رد شده</a>
            <a href="{{ route('admin.gallery.missing') }}" class="menu-button bg-yellow-500">گمشده</a>
            <a href="{{ route('admin.images.checker') }}" class="menu-button bg-purple-500">بررسی تصاویر گمشده</a>
            <a href="{{ url('/dashboard') }}" class="menu-button bg-gray-500">بازگشت به داشبورد</a>
        </div>
    </div>

    <div id="notification" class="notification"></div>

    @if(session('success'))
        <div class="text-center py-3 bg-green-100 text-green-700 mb-4 font-bold">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="text-center py-3 bg-red-100 text-red-700 mb-4 font-bold">{{ session('error') }}</div>
    @endif

    <div class="image-grid" id="image-gallery">
        @forelse ($images as $image)
            <div class="image-item w-1/4 h-[25vw]" data-image-id="{{ $image->id }}">
                <div class="image-container" onclick="showFullscreen('{{ $image->image_url ?? asset('storage/' . $image->image_path) }}')">
                    <span class="status-badge pending-badge">انتظار</span>
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر" onerror="this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="image-details">
                    <div>شناسه: {{ $image->id }}</div>
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

    <div id="fullscreen-container" class="fullscreen" onclick="closeFullscreen()">
        <img id="fullscreen-image" src="" alt="تصویر تمام‌صفحه">
    </div>

    <div class="footer">
        @if(count($images) > 0)
            <button onclick="bulkApprove()" class="bulk-approve bg-green-500 hover:bg-green-600 disabled:bg-green-300 w-full py-3 text-white font-bold text-lg transition">تأیید گروهی همه تصاویر</button>
        @endif
        <div class="text-center py-4">{{ $images->links() }}</div>
    </div>
</div>

<script src="{{ asset('js/gallery.js') }}"></script>
<script>
    const approveImage=e=>processImageAction(e,"{{ url('admin/gallery/approve') }}/"+e,"همه تصاویر بررسی شدند!","تصویر با موفقیت تأیید شد");const rejectImage=e=>processImageAction(e,"{{ url('admin/gallery/reject') }}/"+e,"همه تصاویر بررسی شدند!","تصویر با موفقیت رد شد");const bulkApprove=()=>{const e=Array.from(document.querySelectorAll("#image-gallery [data-image-id]")).map(e=>e.getAttribute("data-image-id"));if(e.length>0&&confirm(`آیا از تأیید ${e.length} تصویر اطمینان دارید؟`)){const t=showNotification("در حال تأیید...","info",0),n=document.querySelector(".bulk-approve");n&&(n.disabled=!0);const o=new FormData;o.append("_token",document.querySelector('meta[name="csrf-token"]').content),e.forEach(e=>o.append("image_ids[]",e)),fetch("{{ url('admin/gallery/bulk-approve') }}",{method:"POST",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content,"Accept":"application/json"},body:o}).then(e=>{if(429===e.status)return e.json().then(e=>{throw{status:429,message:e.message}});if(!e.ok)throw new Error(`خطای HTTP: ${e.status}`);return e.json()}).then(o=>{if(t&&t.remove(),document.querySelectorAll("#image-gallery [data-image-id]").forEach(e=>{e.style.transition="opacity .5s ease, transform .5s ease",e.style.opacity="0",e.style.transform="scale(0.8)"}),setTimeout(()=>{showNotification(`${e.length} تصویر تأیید شدند`,"success");const t=document.getElementById("image-gallery");t.innerHTML='<div class="w-full text-center py-8 bg-green-100 text-green-700 animate-pulse"><p class="font-bold text-lg">تمام تصاویر تأیید شدند!</p><p>لطفاً صفحه را رفرش کنید یا به بخش تصاویر تأیید شده بروید.</p></div><div class="flex justify-center my-4"><button onclick="location.reload()" class="bg-blue-500 text-white px-4 py-2 rounded mx-2 hover:bg-blue-600 transition">بارگذاری مجدد</button><a href="{{ route('admin.gallery.visible') }}" class="bg-green-500 text-white px-4 py-2 rounded mx-2 hover:bg-green-600 transition">مشاهده تصاویر تأیید شده</a></div>',n&&(n.style.display="none")},500)}).catch(e=>{t&&t.remove(),n&&(n.disabled=!1),handleRateLimitError(e,"bulk")||(showNotification("تصاویر تأیید شدند اما خطایی رخ داد. لطفاً رفرش کنید.","warning"),setTimeout(()=>{location.reload()},3e3))})}};document.addEventListener("keydown",e=>{const t=document.querySelector("[data-image-id]");if(!t)return;const n=t.getAttribute("data-image-id");"a"!==e.key&&"A"!==e.key||approveImage(n),"r"!==e.key&&"R"!==e.key||rejectImage(n)});
</script>
</body>
</html>
