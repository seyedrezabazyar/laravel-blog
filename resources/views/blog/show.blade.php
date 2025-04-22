<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $post->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div class="text-sm text-gray-500">
                            <span>{{ $post->created_at->format('Y/m/d') }}</span> |
                            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-blue-500 hover:underline">{{ $post->category->name }}</a> |
                            <span>{{ $post->user->name }}</span>
                        </div>
                        <a href="{{ route('blog.index') }}" class="text-blue-500 hover:underline">بازگشت به وبلاگ</a>
                    </div>

                    @if($post->featured_image)
                        <div class="mb-6">
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full max-h-96 object-cover rounded-lg">
                        </div>
                    @endif

                    <div class="prose max-w-none">
                        {!! $post->content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
