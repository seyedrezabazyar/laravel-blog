// resources/views/blog/index.blade.php
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>کتابستان</title>
</head>
<body>
<h1>کتابستان</h1>
<p>تعداد پست‌ها: {{ count($posts) }}</p>

<h2>پست‌ها:</h2>
<ul>
    @foreach($posts as $post)
        <li>{{ $post->title }}</li>
    @endforeach
</ul>

<h2>دسته‌بندی‌ها:</h2>
<ul>
    @foreach($categories as $category)
        <li>{{ $category->name }}</li>
    @endforeach
</ul>
</body>
</html>
