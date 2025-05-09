@extends('layouts.blog-app')

@section('content')
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">نتایج جستجو برای: {{ $query }}</h1>
                <p class="text-gray-600">{{ $posts->total() }} نتیجه یافت شد</p>
            </div>

            <!-- Search form -->
            <div class="mt-8 max-w-xl mx-auto">
                <form action="{{ route('blog.search') }}" method="GET" class="flex">
                    <input type="text" name="q" placeholder="جستجو در وبلاگ..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ $query }}">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-l-md hover:bg-indigo-700 transition">
                        جستجو
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4 space-x-reverse">
                    <li>
                        <div>
                            <a href="{{ route('blog.index') }}" class="text-gray-500 hover:text-gray-700">وبلاگ</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="mr-2 text-gray-700 font-medium">جستجو</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Search Results -->
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <x-blog-card :post="$post" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $posts->appends(['q' => $query])->links() }}
            </div>
        @else
            <div class="bg-white p-12 rounded-lg shadow-sm text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">نتیجه‌ای یافت نشد</h3>
                <p class="text-gray-500 mb-6">متأسفانه هیچ نتیجه‌ای با عبارت "{{ $query }}" پیدا نشد.</p>
                <div class="flex justify-center">
                    <a href="{{ route('blog.index') }}" class="px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">بازگشت به صفحه اصلی</a>
                </div>
            </div>

            <!-- Popular Posts -->
            @if($popularPosts->count() > 0)
                <div class="mt-16">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">محبوب‌ترین مطالب</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($popularPosts as $post)
                            <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
                                @if($post->featuredImage && !$post->featuredImage->hide_image)
                                    <div class="h-40 overflow-hidden">
                                        <img
                                            src="{{ $post->featuredImage->display_url }}"
                                            alt="{{ $post->title }}"
                                            class="w-full h-full object-cover hover:scale-105 transition duration-500"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';"
                                        >
                                    </div>
                                @else
                                    <div class="h-40 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-indigo-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h4 class="text-lg font-bold mb-2">{{ $post->title }}</h4>
                                    <p class="text-gray-600 text-sm mb-2">{{ Str::limit(strip_tags($post->content), 80) }}</p>
                                    <a href="{{ route('blog.show', $post->slug) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">ادامه مطلب</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
