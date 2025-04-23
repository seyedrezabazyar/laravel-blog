@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
    @if($post->featured_image)
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
        <div class="flex items-center text-sm text-gray-500 mb-2">
            <span>{{ $post->created_at->format('Y/m/d') }}</span>
            <span class="mx-2">•</span>
            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $post->category->name }}</a>
        </div>
        <h3 class="text-xl font-bold mb-3">{{ $post->title }}</h3>
        <p class="text-gray-600 mb-4">{{ Str::limit(strip_tags($post->content), 120) }}</p>
        <div class="flex items-center justify-between">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">ادامه مطلب</a>
            <span class="text-sm text-gray-500">{{ $post->user->name }}</span>
        </div>
    </div>
</div>
