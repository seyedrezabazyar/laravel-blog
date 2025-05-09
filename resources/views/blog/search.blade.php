@extends('layouts.blog-app')

@section('content')
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">نتایج جستجو برای: {{ $query }}</h1>
                <p class="text-gray-600">نتایج یافت شده برای "{{ $query }}"</p>
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
        <!-- Search Results -->
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($posts as $post)
                    <x-blog-card :post="$post" />
                @endforeach
            </div>

            <!-- Pagination - SimplePaginate Version -->
            <div class="mt-10 flex justify-between">
                @if($posts->previousPageUrl())
                    <a href="{{ $posts->previousPageUrl() }}&q={{ $query }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
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
                    <a href="{{ $posts->nextPageUrl() }}&q={{ $query }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">نتیجه‌ای یافت نشد</h3>
                <p class="text-gray-500 mb-6">متأسفانه هیچ نتیجه‌ای با عبارت "{{ $query }}" پیدا نشد.</p>
                <div class="flex justify-center">
                    <a href="{{ route('blog.index') }}" class="px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">بازگشت به صفحه اصلی</a>
                </div>
            </div>
        @endif
    </div>
@endsection
