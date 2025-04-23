@extends('layouts.blog-app')

@section('content')
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Post Header -->
            <div class="p-6 md:p-8">
                <div class="flex items-center text-sm text-gray-500 mb-4">
                    <span>{{ $post->created_at->format('Y/m/d') }}</span>
                    <span class="mx-2">•</span>
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $post->category->name }}</a>
                    @if($post->author)
                        <span class="mx-2">•</span>
                        <a href="{{ route('blog.author', $post->author->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $post->author->name }}</a>
                    @endif
                </div>

                <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 mb-2">{{ $post->title }}</h1>
                @if($post->english_title)
                    <h2 class="text-xl text-gray-600 mb-4">{{ $post->english_title }}</h2>
                @endif

                <!-- Post Navigation -->
                <div class="flex justify-between text-sm mb-6">
                    <a href="{{ route('blog.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        بازگشت به کتاب‌ها
                    </a>

                    <!-- Share Links -->
                    <div class="flex space-x-3 space-x-reverse">
                        <a href="#" class="text-gray-500 hover:text-indigo-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-indigo-600">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Book Details -->
            <div class="p-6 md:p-8 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Book Cover -->
                    <div class="md:col-span-1">
                        @if($post->featured_image && !$post->hide_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-auto object-cover rounded-lg shadow-md">
                        @else
                            <div class="w-full h-64 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-lg shadow-md flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        @endif

                        @if($post->purchase_link)
                            <a href="{{ $post->purchase_link }}" target="_blank" class="mt-4 block w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-center transition">
                                خرید کتاب
                            </a>
                        @endif
                    </div>

                    <!-- Book Info -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold mb-4">مشخصات کتاب</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @if($post->author)
                                <div class="flex items-center">
                                    <span class="font-medium ml-2">نویسنده:</span>
                                    <a href="{{ route('blog.author', $post->author->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                                        {{ $post->author->name }}
                                    </a>
                                </div>
                            @endif

                            @if($post->authors->count() > 0)
                                <div class="flex items-start">
                                    <span class="font-medium ml-2">همکاران:</span>
                                    <div>
                                        @foreach($post->authors as $coAuthor)
                                            <a href="{{ route('blog.author', $coAuthor->slug) }}" class="text-indigo-600 hover:text-indigo-800 block">
                                                {{ $coAuthor->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($post->publisher)
                                <div class="flex items-center">
                                    <span class="font-medium ml-2">ناشر:</span>
                                    <a href="{{ route('blog.publisher', $post->publisher->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                                        {{ $post->publisher->name }}
                                    </a>
                                </div>
                            @endif

                            @if($post->language)
                                <div class="flex items-center">
                                    <span class="font-medium ml-2">زبان:</span>
                                    <span>{{ $post->language }}</span>
                                </div>
                            @endif

                            @if($post->publication_year)
                                <div class="flex items-center">
                                    <span class="font-medium ml-2">سال انتشار:</span>
                                    <span>{{ $post->publication_year }}</span>
                                </div>
                            @endif

                            @if($post->format)
                                <div class="flex items-center">
                                    <span class="font-medium ml-2">فرمت:</span>
                                    <span>{{ $post->format }}</span>
                                </div>
                            @endif

                            @if($post->book_codes)
                                <div class="flex items-start col-span-2">
                                    <span class="font-medium ml-2">شابک:</span>
                                    <span>{{ $post->book_codes }}</span>
                                </div>
                            @endif
                        </div>

                        @if($post->keywords)
                            <div class="mt-6">
                                <h4 class="text-md font-semibold mb-2">کلمات کلیدی:</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(explode(',', $post->keywords) as $keyword)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">
                                            {{ trim($keyword) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Book Content -->
            <div class="p-6 md:p-8">
                <div class="prose max-w-none prose-lg">
                    {!! $post->purified_content !!}
                </div>

                @if($post->english_content)
                    <div class="mt-10 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">English Description</h3>
                        <div class="prose max-w-none prose-lg ltr">
                            {!! $post->english_content !!}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Book Images -->
            @if(count($post->visible_images) > 0)
                <div class="p-6 md:p-8 bg-gray-50 border-t border-gray-200">
                    <h3 class="text-xl font-bold mb-6">تصاویر بیشتر</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($post->visible_images as $image)
                            <div class="bg-white p-2 rounded-lg shadow-md">
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->caption ?? $post->title }}" class="w-full h-auto rounded-md">
                                @if($image->caption)
                                    <div class="text-sm text-gray-600 mt-2 p-2 text-center">{{ $image->caption }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Related Books -->
        <div class="mt-12">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">کتاب‌های مرتبط</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $relatedPost)
                    <x-blog-card :post="$relatedPost" />
                @endforeach
            </div>
        </div>
    </div>
@endsection
