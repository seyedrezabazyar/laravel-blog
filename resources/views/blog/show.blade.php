@extends('layouts.blog-app')

@section('content')
    <!-- نان‌برد و هدر کتاب - بهبود یافته -->
    <div class="bg-gray-50 py-6 mb-6 border-b border-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex items-center text-sm mb-4">
                <a href="{{ route('blog.index') }}" class="text-blue-600 hover:text-blue-800 transition font-medium">خانه</a>
                <span class="mx-2 text-gray-400">›</span>
                <a href="{{ route('blog.category', $post->category->slug) }}" class="text-blue-600 hover:text-blue-800 transition font-medium">{{ $post->category->name }}</a>
                <span class="mx-2 text-gray-400">›</span>
                <span class="text-gray-600">{{ $post->title }}</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 pb-12">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- ستون راست - تصویر و دکمه خرید (30%) -->
            <div class="w-full lg:w-3/10" style="width: 30%;">
                <!-- تصویر کتاب -->
                <div class="card mb-6 overflow-hidden rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
                    <div class="relative">
                        @if($post->featuredImage && !($post->featuredImage->hide_image && !auth()->check()))
                            <img src="{{ $post->featuredImage->display_url }}" alt="{{ $post->title }}" class="w-full h-auto">
                        @else
                            <div class="w-full h-64 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        @endif

                        @if($post->publication_year)
                            <div class="absolute top-4 right-4 bg-white px-3 py-1.5 rounded-md text-sm font-semibold text-gray-700 shadow-sm">
                                {{ $post->publication_year }}
                            </div>
                        @endif

                        @if(auth()->check() && auth()->user()->isAdmin() && $post->featuredImage && $post->featuredImage->hide_image)
                            <div class="absolute bottom-0 left-0 right-0 bg-red-600 text-white text-center py-2 text-sm">
                                این تصویر برای کاربران عادی مخفی است
                            </div>
                        @endif
                    </div>
                </div>

                <!-- دکمه خرید کتاب -->
                @if($post->purchase_link)
                    <div class="mb-6">
                        <a href="{{ $post->purchase_link }}" target="_blank" class="btn btn-primary block text-center py-3 text-lg font-bold rounded-lg transition-all hover:shadow-lg bg-blue-600 hover:bg-blue-700 text-white">
                            خرید کتاب از سایت ناشر
                        </a>
                        <p class="text-xs text-gray-500 text-center mt-2">انتقال به وب‌سایت رسمی ناشر</p>

                        <!-- نمایش باکس وضعیت کشور -->
                        <div class="mt-3 py-2 px-3 rounded-lg text-center text-sm font-medium {{ $isIranianIp ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $isIranianIp ? 'ایران' : 'خارج' }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- ستون چپ - عنوان، اطلاعات و محتوای کتاب (70%) -->
            <div class="w-full lg:w-7/10" style="width: 70%;">
                <!-- عنوان فارسی و انگلیسی -->
                <div class="mb-6">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">{{ $post->title }}</h1>
                    @if($post->english_title)
                        <p class="text-lg text-gray-600">{{ $post->english_title }}</p>
                    @endif
                </div>

                <!-- اطلاعات کتاب به صورت جدول زیبا - بهبود یافته برای خوانایی بیشتر -->
                <div class="card mb-8 overflow-hidden rounded-xl shadow-md bg-white border border-gray-100">
                    <div class="bg-blue-600 py-4 px-6">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            مشخصات کتاب
                        </h2>
                    </div>

                    <div class="p-6">
                        <table class="w-full border-collapse">
                            <tbody>
                            @if($post->author || $post->authors->count() > 0)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">{{ ($post->authors->count() > 0) ? 'نویسندگان' : 'نویسنده' }}</td>
                                    <td class="py-3 px-4">
                                        @if($post->author)
                                            <a href="{{ route('blog.author', $post->author->slug) }}" class="text-gray-800 hover:text-blue-600">
                                                {{ $post->author->name }}
                                            </a>
                                        @endif

                                        @if($post->authors->count() > 0)
                                            @if($post->author) <span class="mx-1">،</span> @endif
                                            @foreach($post->authors as $index => $author)
                                                <a href="{{ route('blog.author', $author->slug) }}" class="text-gray-800 hover:text-blue-600">
                                                    {{ $author->name }}{{ $index < $post->authors->count() - 1 ? '، ' : '' }}
                                                </a>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            @if($post->publisher)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">ناشر</td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('blog.publisher', $post->publisher->slug) }}" class="text-gray-800 hover:text-blue-600">
                                            {{ $post->publisher->name }}
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            @if($post->language)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">زبان</td>
                                    <td class="py-3 px-4">{{ $post->language }}</td>
                                </tr>
                            @endif

                            @if($post->format)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">فرمت</td>
                                    <td class="py-3 px-4">{{ $post->format }}</td>
                                </tr>
                            @endif

                            @if($post->book_codes)
                                <tr>
                                    <td class="py-3 pr-4 text-blue-700 font-medium w-1/4 whitespace-nowrap">شابک</td>
                                    <td class="py-3 px-4">
                                        <code class="bg-gray-50 px-3 py-1 rounded-md font-mono text-sm text-gray-700 select-all border border-gray-200">{{ $post->book_codes }}</code>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- توضیحات فارسی کتاب -->
                <div class="card mb-6 rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="card-header bg-green-600 border-b border-gray-200 py-4 px-6">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            معرفی کتاب
                        </h2>
                    </div>
                    <div class="card-body p-6 bg-white">
                        @if(strip_tags($post->purified_content))
                            <div class="blog-content prose prose-green max-w-none">
                                {!! $post->purified_content !!}
                            </div>
                        @else
                            <div class="text-center py-12 flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <h3 class="text-xl font-bold text-gray-700 mb-2">توضیحاتی برای این کتاب ثبت نشده است</h3>
                                <p class="text-gray-500 max-w-md text-center">می‌توانید برای کسب اطلاعات بیشتر در مورد این کتاب با ناشر تماس بگیرید.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- توضیحات انگلیسی کتاب -->
                @if($post->english_content)
                    <div class="card mb-6 rounded-xl shadow-md overflow-hidden border border-gray-100">
                        <div class="card-header bg-purple-600 border-b border-gray-200 py-4 px-6">
                            <h2 class="text-xl font-bold text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                                Book Description
                            </h2>
                        </div>
                        <div class="card-body p-6 bg-white">
                            <div class="blog-content prose prose-purple max-w-none ltr" dir="ltr">
                                {!! $post->english_content !!}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- برچسب‌های پست -->
                @if($post->tags && $post->tags->count() > 0)
                    <div class="card mb-6 rounded-xl shadow-md overflow-hidden border border-gray-100">
                        <div class="card-header bg-blue-600 border-b border-gray-200 py-4 px-6">
                            <h2 class="text-xl font-bold text-white flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                برچسب‌های کتاب
                            </h2>
                        </div>
                        <div class="card-body p-6 bg-white">
                            <div class="flex flex-wrap gap-2">
                                @foreach($post->tags as $tag)
                                    <a href="{{ route('blog.tag', $tag->slug) }}" class="badge px-4 py-2 rounded-full text-sm bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors duration-200">
                                        {{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- کتاب‌های مشابه - 4 ستونه - بهبود یافته -->
        <div class="mt-12">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    کتاب‌های مشابه
                </h2>
                <a href="{{ route('blog.index') }}" class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                    مشاهده بیشتر
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedPosts->take(4) as $relatedPost)
                    <div class="group">
                        <a href="{{ route('blog.show', $relatedPost->slug) }}" class="block">
                            <div class="overflow-hidden rounded-xl shadow-md hover:shadow-xl transition-all duration-300 bg-white border border-gray-100 transform group-hover:-translate-y-1">
                                <div class="aspect-w-3 aspect-h-4 overflow-hidden">
                                    @if($relatedPost->featuredImage && !($relatedPost->featuredImage->hide_image && !auth()->check()))
                                        <img src="{{ $relatedPost->featuredImage->display_url }}" alt="{{ $relatedPost->title }}"
                                             class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-48 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-gray-900 group-hover:text-blue-600 text-lg mb-1 line-clamp-2 transition-colors duration-200">
                                        {{ $relatedPost->title }}
                                    </h3>
                                    @if($relatedPost->publication_year)
                                        <div class="mt-2">
                                            <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-md text-xs font-medium">
                                                {{ $relatedPost->publication_year }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        /* اضافه کردن استایل‌های سفارشی برای عرض ستون‌ها */
        @media (min-width: 1024px) {
            .lg\:w-3\/10 {
                width: 30%;
            }
            .lg\:w-7\/10 {
                width: 70%;
            }
        }
    </style>
@endsection
