@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
    @if($post->featured_image && !$post->hide_image)
        <div class="overflow-hidden h-48">
            <img
                src="{{ $post->featured_image_url }}"
                alt="{{ $post->title }}"
                class="w-full h-full object-cover hover:scale-105 transition duration-500">
        </div>
    @else
        <div class="overflow-hidden h-48 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
            <svg class="w-16 h-16 text-indigo-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
            </svg>
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
            @if($post->author || $post->authors->count() > 0)
                <span>
                    {{ ($post->authors->count() > 0) ? 'نویسندگان:' : 'نویسنده:' }}
                    @if($post->author)
                        <a href="{{ route('blog.author', $post->author->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ $post->author->name }}
                        </a>
                    @endif

                    @if($post->authors->count() > 0)
                        @if($post->author) <span class="mx-1">،</span> @endif
                        @foreach($post->authors as $index => $author)
                            <a href="{{ route('blog.author', $author->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $author->name }}{{ $index < $post->authors->count() - 1 ? '، ' : '' }}
                            </a>
                        @endforeach
                    @endif
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
