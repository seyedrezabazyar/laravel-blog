@extends('layouts.blog-app')
@section('content')
    @if(auth()->check() && auth()->user()->isAdmin() && $post->hide_content)
        <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-4 rounded-md flex items-center">
            <svg class="h-6 w-6 ml-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>توجه: این پست مخفی است و فقط برای مدیران قابل مشاهده است.</span>
        </div>
    @endif

    <div class="bg-gray-50 py-4 mb-4 border-b border-gray-100">
        <div class="container mx-auto px-4">
            <nav class="flex items-center text-sm">
                <a href="{{ route('blog.index') }}" class="text-blue-600 hover:text-blue-800 transition font-medium">خانه</a>
                <span class="mx-2 text-gray-400">›</span>
                @if($post->category)
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="text-blue-600 hover:text-blue-800 transition font-medium">
                        {{ $post->elasticsearch_category ?: $post->category->name }}
                    </a>
                @else
                    <span class="text-gray-600">{{ $post->elasticsearch_category ?: 'بدون دسته‌بندی' }}</span>
                @endif
                <span class="mx-2 text-gray-400">›</span>
                <span class="text-gray-600">{{ $post->elasticsearch_title ?: $post->title }}</span>
            </nav>
        </div>
    </div>

    <div class="container mx-auto px-4 pb-6 flex flex-col lg:flex-row gap-6">
        <div class="w-full lg:w-3/10">
            <div class="mb-6 rounded-xl shadow hover:shadow-lg transition-shadow">
                @php
                    $isAdmin = auth()->check() && auth()->user()->isAdmin();
                    $featuredImage = $post->featuredImage;
                    $imageUrl = $featuredImage ? $featuredImage->display_url : asset('images/default-book.png');
                    $isHidden = $isAdmin && $featuredImage && $featuredImage->isHidden();
                @endphp
                <div class="relative">
                    <img src="{{ $imageUrl }}" alt="{{ $post->elasticsearch_title ?: $post->title }}" class="w-full h-auto rounded-t-xl" loading="lazy" onerror="this.src='{{ asset('images/default-book.png') }}'">
                    @if($isAdmin && $isHidden)
                        <div class="absolute inset-0 bg-red-500/20 flex items-center justify-center">
                            <span class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-bold shadow">تصویر مخفی شده است</span>
                        </div>
                    @endif
                    @if($post->elasticsearch_publication_year ?: $post->publication_year)
                        <div class="absolute top-4 right-4 bg-white px-3 py-1.5 rounded-md text-sm font-semibold text-gray-700 shadow-sm">
                            {{ $post->elasticsearch_publication_year ?: $post->publication_year }}
                        </div>
                    @endif
                </div>
            </div>
            @if($post->purchase_link)
                <div class="mb-6">
                    <a href="{{ $post->purchase_link }}" target="_blank" rel="noopener" class="block text-center py-3 text-lg font-bold rounded-lg transition hover:shadow-lg bg-blue-600 hover:bg-blue-700 text-white">خرید کتاب از سایت ناشر</a>
                    <p class="text-xs text-gray-500 text-center mt-2">انتقال به وب‌سایت رسمی ناشر</p>
                </div>
            @endif
        </div>

        <div class="w-full lg:w-7/10">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $post->elasticsearch_title ?: $post->title }}</h1>
                @if($post->english_title)
                    <p class="text-lg text-gray-600">{{ $post->english_title }}</p>
                @endif
            </div>

            <div class="mb-6 rounded-xl shadow border border-gray-100">
                <div class="bg-blue-600 py-4 px-6">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V6a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        مشخصات کتاب
                    </h2>
                </div>
                <div class="p-4">
                    <table class="w-full border-collapse">
                        <tbody>
                        @if($post->elasticsearch_category || ($post->category && $post->category->name))
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">موضوع اصلی</td>
                                <td class="py-3 px-4">
                                    @if($post->category)
                                        <a href="{{ route('blog.category', $post->category->slug) }}" class="text-gray-800 hover:text-blue-600">
                                            {{ $post->elasticsearch_category ?: $post->category->name }}
                                        </a>
                                    @else
                                        <span class="text-gray-800">{{ $post->elasticsearch_category }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr class="border-b border-gray-100">
                            <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">نوع کالا</td>
                            <td class="py-3 px-4">کتاب الکترونیکی</td>
                        </tr>
                        @if($post->elasticsearch_publisher || ($post->publisher && $post->publisher->name))
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">ناشر</td>
                                <td class="py-3 px-4">
                                    @if($post->publisher)
                                        <a href="{{ route('blog.publisher', $post->publisher->slug) }}" class="text-gray-800 hover:text-blue-600">
                                            {{ $post->elasticsearch_publisher ?: $post->publisher->name }}
                                        </a>
                                    @else
                                        <span class="text-gray-800">{{ $post->elasticsearch_publisher }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if($post->elasticsearch_isbn || $post->isbn)
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">کد کتاب (شابک)</td>
                                <td class="py-3 px-4">
                                    <div class="text-gray-700 font-mono text-sm">{{ $post->elasticsearch_isbn ?: $post->isbn }}</div>
                                </td>
                            </tr>
                        @endif
                        @if($post->elasticsearch_author || $post->author || $post->authors->count() > 0)
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">
                                    {{ ($post->authors && $post->authors->count() > 0) ? 'نویسندگان' : 'نویسنده' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($post->elasticsearch_author)
                                        {{ $post->elasticsearch_author }}
                                    @else
                                        @if($post->author)
                                            <a href="{{ route('blog.author', $post->author->slug) }}" class="text-gray-800 hover:text-blue-600">{{ $post->author->name }}</a>
                                            @if($post->authors->count() > 0)<span class="mx-1">،</span>@endif
                                        @endif
                                        @foreach($post->authors as $index => $author)
                                            <a href="{{ route('blog.author', $author->slug) }}" class="text-gray-800 hover:text-blue-600">{{ $author->name }}{{ $index < $post->authors->count() - 1 ? '، ' : '' }}</a>
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if($post->elasticsearch_language || $post->languages)
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">زبان کتاب</td>
                                <td class="py-3 px-4">{{ $post->elasticsearch_language ?: $post->languages }}</td>
                            </tr>
                        @endif
                        @if($post->elasticsearch_format || $post->format)
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">فرمت فایل</td>
                                <td class="py-3 px-4">{{ strtoupper($post->elasticsearch_format ?: $post->format) }}</td>
                            </tr>
                        @endif
                        @if($post->elasticsearch_pages_count || $post->pages_count)
                            <tr class="border-b border-gray-100">
                                <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">تعداد صفحات</td>
                                <td class="py-3 px-4">{{ number_format($post->elasticsearch_pages_count ?: $post->pages_count) }} صفحه</td>
                            </tr>
                        @endif
                        @if($post->elasticsearch_publication_year || $post->publication_year)
                            <tr>
                                <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">سال انتشار</td>
                                <td class="py-3 px-4">{{ $post->elasticsearch_publication_year ?: $post->publication_year }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mb-6 rounded-xl shadow border border-gray-100">
                <div class="bg-green-600 py-4 px-6">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        معرفی کتاب
                    </h2>
                </div>
                <div class="p-6 bg-white">
                    @if(strip_tags($post->purified_content))
                        <div class="prose prose-green max-w-none text-justify leading-relaxed">
                            {!! nl2br(e($post->purified_content)) !!}
                        </div>
                    @else
                        <div class="text-center py-12 flex flex-col items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <h3 class="text-xl font-bold text-gray-700 mb-2">توضیحاتی ثبت نشده است</h3>
                            <p class="text-gray-500 max-w-md">برای اطلاعات بیشتر با ناشر تماس بگیرید.</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($post->english_content)
                <div class="mb-6 rounded-xl shadow border border-gray-100">
                    <div class="bg-blue-600 py-4 px-6" dir="ltr">
                        <h2 class="text-xl font-bold text-white flex items-center justify-end">
                            Book Description
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                        </h2>
                    </div>
                    <div class="p-6 bg-white" dir="ltr" lang="en">
                        @if(strip_tags($post->english_content))
                            <div class="prose prose-blue max-w-none text-justify leading-relaxed">
                                {!! nl2br(e($post->english_content)) !!}
                            </div>
                        @else
                            <div class="text-center py-12 flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <h3 class="text-xl font-bold text-gray-700 mb-2">No English description available</h3>
                                <p class="text-gray-500 max-w-md">Please contact the publisher for more information.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- بخش کتاب‌های مشابه -->
    <div class="container mx-auto px-4 mt-8">
        <div class="flex items-center justify-between mb-6 bg-gray-50 py-4 px-6 rounded-xl shadow-sm">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                کتاب‌های مشابه
            </h2>
            @if($post->category)
                <a href="{{ route('blog.category', $post->category->slug) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center transition shadow-sm hover:shadow">
                    مشاهده بیشتر
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endif
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($relatedPosts->take(4) as $relatedPost)
                <div class="group">
                    <a href="{{ route('blog.show', $relatedPost->slug) }}" class="block">
                        <div class="rounded-xl shadow hover:shadow-lg transition-all bg-white border border-gray-100 group-hover:-translate-y-1">
                            <div class="aspect-[2/3] relative overflow-hidden">
                                @php
                                    $isAdmin = auth()->check() && auth()->user()->isAdmin();
                                    $relatedImage = $relatedPost->featuredImage;
                                    $imageUrl = $relatedImage ? $relatedImage->display_url : asset('images/default-book.png');
                                    $isHidden = $isAdmin && $relatedImage && $relatedImage->isHidden();
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $relatedPost->title }}" class="w-full h-full object-cover" loading="lazy" onerror="this.src='{{ asset('images/default-book.png') }}'">
                                @if($isAdmin && $isHidden)
                                    <div class="absolute inset-0 bg-red-500/20 flex items-center justify-center">
                                        <span class="bg-red-600 text-white px-2 py-1 rounded-md text-xs font-bold shadow">تصویر مخفی شده</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-medium text-gray-900 line-clamp-2">{{ $relatedPost->title }}</h3>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/blog.css') }}">
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .prose {
            line-height: 1.8;
        }
        .text-justify {
            text-align: justify;
        }
    </style>
@endpush

@push('scripts')
    <script defer src="{{ asset('js/blog.js') }}"></script>
@endpush
