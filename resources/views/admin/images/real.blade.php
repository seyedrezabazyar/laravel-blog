<!DOCTYPE html>
<html>
<head>
    <title>تصاویر واقعی</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">تصاویر واقعی (کد 200)</h1>
    <div class="mb-4">
        <button onclick="bulkApprove()" class="bg-blue-500 text-white px-4 py-2 rounded">تأیید همه</button>
    </div>
    <div class="grid grid-cols-4 gap-4" id="image-gallery">
        @foreach ($validImages as $image)
            <div class="bg-white p-4 rounded shadow" data-image-id="{{ $image->id }}">
                <img src="{{ $image->image_path }}" alt="تصویر" class="w-full h-48 object-cover">
                <div class="mt-2 flex justify-between">
                    <button onclick="approveImage({{ $image->id }})" class="bg-green-500 text-white px-4 py-2 rounded">تأیید</button>
                    <button onclick="rejectImage({{ $image->id }})" class="bg-red-500 text-white px-4 py-2 rounded">رد</button>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $images->links() }}
    </div>
</div>

<script>
    // تنظیم CSRF token برای درخواست‌های AJAX
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';

    // تابع برای تأیید تصویر
    function approveImage(imageId) {
        axios.post('/admin/gallery/approve/' + imageId)
            .then(response => {
                if (response.data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                }
            })
            .catch(error => {
                console.error(error);
            });
    }

    // تابع برای رد تصویر
    function rejectImage(imageId) {
        axios.post('/admin/gallery/reject/' + imageId)
            .then(response => {
                if (response.data.success) {
                    document.querySelector(`[data-image-id="${imageId}"]`).remove();
                }
            })
            .catch(error => {
                console.error(error);
            });
    }

    // تابع برای تأیید گروهی تصاویر
    function bulkApprove() {
        const imageIds = Array.from(document.querySelectorAll('#image-gallery [data-image-id]'))
            .map(element => element.getAttribute('data-image-id'));
        if (imageIds.length > 0) {
            axios.post('/admin/gallery/bulk-approve', { image_ids: imageIds })
                .then(response => {
                    if (response.data.success) {
                        // حذف همه تصاویر صفحه فعلی
                        document.querySelectorAll('#image-gallery [data-image-id]').forEach(element => element.remove());
                    }
                })
                .catch(error => {
                    console.error(error);
                });
        }
    }
</script>
</body>
</html>
