<!DOCTYPE html>
<html>
<head>
    <title>تصاویر تأیید شده</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">تصاویر تأیید شده</h1>
    <div class="grid grid-cols-4 gap-4">
        @foreach ($images as $image)
            <div class="bg-white p-4 rounded shadow">
                <img src="{{ $image->image_path }}" alt="تصویر" class="w-full h-48 object-cover">
                <p class="mt-2">وضعیت: {{ $image->hide_image }}</p>
            </div>
        @endforeach
    </div>
    <div class="mt-4">
        {{ $images->links() }}
    </div>
</div>
</body>
</html>
