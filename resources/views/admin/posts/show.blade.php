<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('مشاهده پست') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">{{ $post->title }}</h1>
                        <div>
                            <a href="{{ route('admin.posts.edit', $post) }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 ml-2">ویرایش</a>
                            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">بازگشت</a>
                        </div>
                    </div>

                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between mb-2">
                            <div>
                                <span class="font-bold">دسته‌بندی:</span> {{ $post->category->name }}
                            </div>
                            <div>
                                <span class="font-bold">نویسنده:</span> {{ $post->user->name }}
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <div>
                                <span class="font-bold">تاریخ ایجاد:</span> {{ $post->created_at->format('Y/m/d H:i') }}
                            </div>
                            <div>
                                <span class="font-bold">وضعیت:</span>
                                @if($post->is_published)
                                    <span class="text-green-600">منتشر شده</span>
                                @else
                                    <span class="text-yellow-600">پیش‌نویس</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($post->featured_image)
                        <div class="mb-6">
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full max-h-96 object-cover rounded-lg">
                        </div>
                    @endif

                    <div class="prose max-w-none">
                        {!! $post->content !!}
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-blue-500 hover:underline" target="_blank">مشاهده در وبلاگ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
