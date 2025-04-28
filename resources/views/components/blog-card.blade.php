@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
    <div class="overflow-hidden h-48">
        @if($post->featuredImage && !($post->featuredImage->hide_image && !auth()->check()))
            <img
                src="{{ $post->featuredImage->display_url }}"
                alt="{{ $post->title }}"
                class="w-full h-full object-cover hover:scale-105 transition duration-500">
        @else
            <div class="w-full h-48 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        @endif
    </div>

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

        @if(auth()->check() && auth()->user()->isAdmin() && $post->featuredImage && $post->featuredImage->hide_image)
            <div class="mt-2 bg-red-100 text-red-700 text-xs px-2 py-1 rounded-md text-center">
                تصویر مخفی شده است
            </div>
        @endif
    </div>
</div>
