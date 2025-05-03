@extends('layouts.blog-app')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-50 to-purple-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 fade-in">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">اکتشاف دنیای کتاب‌ها</h1>
                    <p class="text-xl text-gray-600 mb-8">با کتابستان، دنیایی از دانش و الهام را کشف کنید. ما برترین کتاب‌ها
                        را به شما معرفی می‌کنیم.</p>
                    <div class="flex flex-row-reverse gap-4">
                        <a href="#latest-posts"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg text-lg font-medium transition duration-150">جدیدترین
                            کتاب‌ها</a>
                        <a href="{{ route('blog.categories') }}"
                            class="bg-white hover:bg-gray-100 text-indigo-600 py-3 px-6 rounded-lg text-lg font-medium transition duration-150 border border-indigo-200">دسته‌بندی‌ها</a>
                    </div>
                </div>
                <div class="md:w-1/2 mt-10 md:mt-0 fade-in" style="animation-delay: 0.3s;">
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80"
                            alt="کتابخانه" class="rounded-lg shadow-xl w-full h-auto"
                            onerror="this.onerror=null; this.src='{{ asset('images/default-hero.png') }}'; if(!this.src.includes('default-hero.png')) this.src='{{ asset('images/default-book.png') }}';">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-16 bg-gradient-to-r from-indigo-50 to-purple-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">دسته‌بندی‌های کتاب</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- باکس دسته‌بندی 1 -->
                <a href="{{ url('/category/رمان') }}"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">رمان</h3>
                    <p class="text-gray-500">داستان‌های خیال‌انگیز</p>
                </a>

                <!-- باکس دسته‌بندی 2 -->
                <a href="{{ url('/category/علمی') }}"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10.496 2.132a1 1 0 00-.992 0l-7 4A1 1 0 003 8v7a1 1 0 100 2h14a1 1 0 100-2V8a1 1 0 00.496-1.868l-7-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">علمی</h3>
                    <p class="text-gray-500">دانش و پژوهش</p>
                </a>

                <!-- باکس دسته‌بندی 3 -->
                <a href="{{ url('/category/تاریخی') }}"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">تاریخی</h3>
                    <p class="text-gray-500">گذشته را بشناسید</p>
                </a>

                <!-- باکس دسته‌بندی 4 -->
                <a href="{{ url('/category/فلسفه') }}"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">فلسفه</h3>
                    <p class="text-gray-500">اندیشه و تفکر</p>
                </a>

                <!-- باکس دسته‌بندی 5 -->
                <a href="{{ url('/category/روانشناسی') }}"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">روانشناسی</h3>
                    <p class="text-gray-500">شناخت ذهن و رفتار</p>
                </a>

                <!-- باکس دسته‌بندی 6 -->
                <a href="{{ url('/category/کودک') }}"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">کودک</h3>
                    <p class="text-gray-500">برای نسل آینده</p>
                </a>

                <!-- باکس دسته‌بندی 7 -->
                <a href="{{ url('/category/موفقیت') }}"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z"
                                clip-rule="evenodd"></path>
                            <path
                                d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">موفقیت</h3>
                    <p class="text-gray-500">توسعه فردی و حرفه‌ای</p>
                </a>

                <!-- باکس دسته‌بندی 8 -->
                <a href="{{ url('/category/هنر') }}"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">هنر</h3>
                    <p class="text-gray-500">خلاقیت و زیبایی</p>
                </a>
            </div>
            <div class="text-center mt-10">
                <a href="{{ route('blog.categories') }}"
                    class="inline-block px-6 py-3 border border-indigo-200 bg-white hover:bg-gray-50 text-indigo-600 rounded-lg text-lg font-medium transition">مشاهده
                    همه دسته‌بندی‌ها</a>
            </div>
        </div>
    </section>

    <!-- Latest Posts Section -->
    <section id="latest-posts" class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">تازه‌ترین کتاب‌های ما</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($posts as $post)
                    <x-blog-card :post="$post" />
                @empty
                    <div class="col-span-3 text-center py-10">
                        <p class="text-gray-500">هیچ کتابی یافت نشد.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Quote Section - بخش ساده و زیبا -->
    <section class="book-quote-section py-20 bg-gradient-to-r from-indigo-700 to-indigo-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="book-quote-container bg-white/10 rounded-xl p-10 text-center shadow-lg">
                <div class="book-quote-icon-wrapper">
                    <svg class="book-quote-icon" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z" />
                    </svg>
                </div>
                <blockquote class="book-quote-text">
                    کتاب‌ها تنها اشیایی هستند که می‌توانید امروز خریداری کنید و تا آخر عمر از آن‌ها لذت ببرید.
                </blockquote>
                <div class="book-quote-divider"></div>
                <p class="book-quote-author">— وارن بافت</p>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function handleImageError(img) {
                img.onerror = null;
                img.src = '{{ asset('images/default-book.png') }}';
            }

            document.querySelectorAll('img').forEach(function(img) {
                img.addEventListener('error', function() {
                    handleImageError(this);
                });
            });
        });
    </script>

    <style>
        .book-quote-section {
            position: relative;
            overflow: hidden;
        }

        .book-quote-container {
            transition: transform 0.3s ease;
        }

        .book-quote-container:hover {
            transform: translateY(-5px);
        }

        .book-quote-icon-wrapper {
            margin-bottom: 1.5rem;
        }

        .book-quote-icon {
            width: 3rem;
            height: 3rem;
            color: rgba(255, 255, 255, 0.8);
            display: inline-block;
        }

        .book-quote-text {
            font-size: 1.5rem;
            line-height: 1.6;
            font-weight: 500;
            color: white;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .book-quote-text {
                font-size: 1.875rem;
            }
        }

        .book-quote-divider {
            width: 4rem;
            height: 2px;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.3));
            margin: 1.5rem auto;
            border-radius: 2px;
        }

        .book-quote-author {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }
    </style>
@endsection
