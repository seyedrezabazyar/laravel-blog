@extends('layouts.blog-app')

@section('content')
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $author->name }}</h1>
                @php
                    // استفاده از فیلدهای شمارنده به جای کوئری اضافی
                    $postsCount = $author->total_posts_count;
                @endphp
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- مسیر دسترسی -->
        <div class="mb-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4 space-x-reverse">
                    <li>
                        <div>
                            <a href="{{ route('blog.index') }}" class="text-gray-500 hover:text-gray-700">صفحه اصلی</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="mr-2 text-gray-700 font-medium">نویسنده</span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="mr-2 text-gray-700 font-medium">{{ $author->name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- معرفی نویسنده -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-12">
            <div class="md:flex">
                <!-- تصویر نویسنده - با lazy loading -->
                <div class="md:w-1/3 p-6">
                    @if($author->image)
                        <img
                            src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                            data-src="{{ asset('storage/' . $author->image) }}"
                            alt="{{ $author->name }}"
                            class="w-full h-auto rounded-lg shadow-md mx-auto max-w-xs lazyload"
                            loading="lazy"
                            onload="this.onload=null; if(this.dataset.src) {this.src=this.dataset.src; delete this.dataset.src;}"
                        >
                    @else
                        <div class="w-full h-64 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-lg shadow-md flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- اطلاعات نویسنده -->
                <div class="md:w-2/3 p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">درباره {{ $author->name }}</h2>

                    @if($author->biography)
                        <div class="prose max-w-none text-gray-600 mb-6">
                            {!! nl2br(e($author->biography)) !!}
                        </div>
                    @else
                        <p class="text-gray-500 italic mb-6">اطلاعات بیوگرافی برای این نویسنده ثبت نشده است.</p>
                    @endif

                    <!-- آمار کتاب‌ها - استفاده از شمارنده‌ها -->
                    <div class="flex items-center text-sm text-gray-500 mt-4">
                        @if($author->posts_count > 0)
                            <div class="flex items-center ml-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>{{ $author->posts_count }} کتاب اصلی</span>
                            </div>
                        @endif

                        @if($author->coauthored_count > 0)
                            <div class="flex items-center ml-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>{{ $author->coauthored_count }} کتاب همکاری</span>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <!-- کتاب‌های نویسنده -->
        <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-6">کتاب‌های {{ $author->name }}</h3>

            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <x-blog-card :post="$post" />
                    @endforeach
                </div>

                <!-- صفحه‌بندی - بدون اجرای کوئری اضافی -->
                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="bg-white p-12 rounded-lg shadow-sm text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ کتابی برای این نویسنده یافت نشد</h3>
                    <p class="text-gray-500">به زودی کتاب‌های این نویسنده اضافه خواهد شد.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <!-- اسکریپت برای lazy loading تصاویر -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // اگر اینترسکشن آبزرور پشتیبانی شود
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const lazyImage = entry.target;
                            const src = lazyImage.getAttribute('data-src');
                            if (src) {
                                lazyImage.src = src;
                                lazyImage.removeAttribute('data-src');
                            }
                            imageObserver.unobserve(lazyImage);
                        }
                    });
                });

                const lazyImages = document.querySelectorAll('img.lazyload');
                lazyImages.forEach(image => {
                    imageObserver.observe(image);
                });
            }
        });
    </script>
@endpush
