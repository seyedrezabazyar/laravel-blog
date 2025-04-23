<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('مشاهده پست') }}
            </h2>
            <div class="flex space-x-2 space-x-reverse">
                <a href="{{ route('admin.posts.edit', $post) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    ویرایش
                </a>
                <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    بازگشت
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $post->title }}</h1>
                        <div class="flex flex-wrap items-center text-sm text-gray-500 mb-4">
                            <span class="flex items-center ml-4 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ $post->created_at->format('Y/m/d H:i') }}
                            </span>
                            <span class="flex items-center ml-4 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <a href="{{ route('blog.category', $post->category->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $post->category->name }}
                                </a>
                            </span>
                            <span class="flex items-center ml-4 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $post->user->name }}
                            </span>
                            <span class="flex items-center mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="{{ $post->is_published ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $post->is_published ? 'منتشر شده' : 'پیش‌نویس' }}
                                </span>
                            </span>
                        </div>
                    </div>

                    @if($post->featured_image)
                        <div class="mb-8">
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full max-h-96 object-cover rounded-xl shadow-md">
                        </div>
                    @endif

                    <div class="prose prose-lg max-w-none rtl mb-6 p-6 bg-gray-50 rounded-lg">
                        {!! $post->purified_content !!}
                    </div>

                    <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                        <div>
                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                </svg>
                                مشاهده در وبلاگ
                            </a>
                        </div>
                        <div class="flex space-x-2 space-x-reverse">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150">
                                ویرایش
                            </a>
                            <form class="inline-block" action="{{ route('admin.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('آیا از حذف این پست اطمینان دارید؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-150">
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
