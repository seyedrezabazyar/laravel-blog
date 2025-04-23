@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
    @if($post->featured_image && !$post->hide_image)
        <div class="overflow-hidden">
            <img
                src="{{ asset('storage/' . $post->featured_image) }}"
                alt="{{ $post->title }}"
                class="custom-square-image">
        </div>
    @else
        <div class="overflow-hidden">
            <img
                src="{{ url('http://127.0.0.1:8000/images/default-book.png') }}"
                alt="تصویر پیش‌فرض کتاب"
                class="custom-square-image">
        </div>
    @endif

    <div class="p-6">
        <!-- دسته‌بندی و سال انتشار -->
        <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                {{ $post->category->name }}
            </a>
            @if($post->publication_year)
                <span>{{ $post->publication_year }}</span>
            @endif
        </div>

        <!-- عنوان کتاب -->
        <h3 class="text-xl font-bold mb-2">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-gray-900 hover:text-indigo-600">
                {{ $post->title }}
            </a>
        </h3>

        <!-- اطلاعات نویسنده و ناشر -->
        <div class="text-sm text-gray-600 mb-4">
            @if($post->author)
                <span>
                    نویسنده:
                    <a href="{{ route('blog.author', $post->author->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                        {{ $post->author->name }}
                    </a>
                </span>
            @endif

            @if($post->publisher)
                <span class="mx-1">•</span>
                <span>
                    ناشر:
                    <a href="{{ route('blog.publisher', $post->publisher->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                        {{ $post->publisher->name }}
                    </a>
                </span>
            @endif
        </div>

        <!-- خلاصه محتوا -->
        <p class="text-gray-600 mb-4">
            {{ Str::limit(strip_tags($post->content), 120) }}
        </p>

        <!-- ادامه مطلب و فرمت -->
        <div class="flex items-center justify-between">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                ادامه مطلب
            </a>
            @if($post->format)
                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">
                    {{ $post->format }}
                </span>
            @endif
        </div>
    </div>
</div>
