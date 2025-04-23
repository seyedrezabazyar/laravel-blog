@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
    @if($post->featured_image && !$post->hide_image)
        <div class="h-48 overflow-hidden">
            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
        </div>
    @else
        <div class="h-48 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
            <svg class="w-16 h-16 text-indigo-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
            </svg>
        </div>
    @endif
    <div class="p-6">
        <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $post->category->name }}</a>
            @if($post->publication_year)
                <span>{{ $post->publication_year }}</span>
            @endif
        </div>
        <h3 class="text-xl font-bold mb-2">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-gray-900 hover:text-indigo-600">
                {{ $post->title }}
            </a>
        </h3>

        <div class="text-sm text-gray-600 mb-4">
            @if($post->author)
                <span>
                    نویسنده: <a href="{{ route('blog.author', $post->author->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $post->author->name }}</a>
                </span>
            @endif

            @if($post->publisher)
                <span class="mx-1">•</span>
                <span>
                    ناشر: <a href="{{ route('blog.publisher', $post->publisher->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $post->publisher->name }}</a>
                </span>
            @endif
        </div>

        <p class="text-gray-600 mb-4">{{ Str::limit(strip_tags($post->content), 120) }}</p>
        <div class="flex items-center justify-between">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">ادامه مطلب</a>
            @if($post->format)
                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $post->format }}</span>
            @endif
        </div>
    </div>
</div>
