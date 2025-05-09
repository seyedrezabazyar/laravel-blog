@extends('layouts.blog-app')

@section('content')
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-600">{{ $postsCount ?? $posts->count() }} کتاب از این ناشر</p>
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
                            <span class="mr-2 text-gray-700 font-medium">ناشران</span>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="mr-2 text-gray-700 font-medium">{{ $publisher->name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- معرفی ناشر -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-12">
            <div class="md:flex">
                <!-- لوگوی ناشر -->
                <div class="md:w-1/3 p-6 flex items-center justify-center">
                    @if($publisher->logo)
                        <img src="{{ asset('storage/' . $publisher->logo) }}" alt="{{ $publisher->name }}" class="max-h-48 max-w-full rounded-lg shadow-md">
                    @else
                        <div class="w-48 h-48 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-lg shadow-md flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- اطلاعات ناشر -->
                <div class="md:w-2/3 p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">درباره {{ $publisher->name }}</h2>

                    @if($publisher->description)
                        <div class="prose max-w-none text-gray-600 mb-6">
                            {!! nl2br(e($publisher->description)) !!}
                        </div>
                    @else
                        <p class="text-gray-500 italic mb-6">اطلاعات توضیحات برای این ناشر ثبت نشده است.</p>
                    @endif

                </div>
            </div>
        </div>

        <!-- کتاب‌های ناشر -->
        <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-6">کتاب‌های {{ $publisher->name }}</h3>

            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <x-blog-card :post="$post" />
                    @endforeach
                </div>

                <!-- صفحه‌بندی -->
                <div class="mt-10 flex justify-between">
                    @if($posts->previousPageUrl())
                        <a href="{{ $posts->previousPageUrl() }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
            <span class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                صفحه قبلی
            </span>
                        </a>
                    @else
                        <span class="px-4 py-2 bg-indigo-300 text-white rounded-md cursor-not-allowed">
            <span class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                صفحه قبلی
            </span>
        </span>
                    @endif

                    @if($posts->hasMorePages())
                        <a href="{{ $posts->nextPageUrl() }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
            <span class="flex items-center">
                صفحه بعدی
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </span>
                        </a>
                    @else
                        <span class="px-4 py-2 bg-indigo-300 text-white rounded-md cursor-not-allowed">
            <span class="flex items-center">
                صفحه بعدی
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        </span>
                    @endif
                </div>
            @else
                <div class="bg-white p-12 rounded-lg shadow-sm text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ کتابی برای این ناشر یافت نشد</h3>
                    <p class="text-gray-500">به زودی کتاب‌های این ناشر اضافه خواهد شد.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
