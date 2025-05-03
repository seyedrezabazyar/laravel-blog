@extends('layouts.blog-app')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-50 to-purple-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 fade-in">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">اکتشاف دنیای کتاب‌ها</h1>
                    <p class="text-xl text-gray-600 mb-8">با کتابستان، دنیایی از دانش و الهام را کشف کنید. ما برترین کتاب‌ها را به شما معرفی می‌کنیم.</p>
                    <div class="flex flex-row-reverse gap-4">
                        <a href="#latest-posts" class="bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg text-lg font-medium transition duration-150">جدیدترین کتاب‌ها</a>
                        <a href="{{ route('blog.categories') }}" class="bg-white hover:bg-gray-100 text-indigo-600 py-3 px-6 rounded-lg text-lg font-medium transition duration-150 border border-indigo-200">دسته‌بندی‌ها</a>
                    </div>
                </div>
                <div class="md:w-1/2 mt-10 md:mt-0 fade-in" style="animation-delay: 0.3s;">
                    <div class="relative">
                        <img
                            src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80"
                            alt="کتابخانه"
                            class="rounded-lg shadow-xl w-full h-auto"
                            onerror="this.onerror=null; this.src='{{ asset('images/default-hero.png') }}'; if(!this.src.includes('default-hero.png')) this.src='{{ asset('images/default-book.png') }}';"
                        >
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
                @foreach($categories as $category)
                    <a href="{{ route('blog.category', $category->slug) }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                        <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $category->name }}</h3>
                        <p class="text-gray-500">{{ $category->posts_count }} کتاب</p>
                    </a>
                @endforeach
            </div>
            <div class="text-center mt-10">
                <a href="{{ route('blog.categories') }}" class="inline-block px-6 py-3 border border-indigo-200 bg-white hover:bg-gray-50 text-indigo-600 rounded-lg text-lg font-medium transition">مشاهده همه دسته‌بندی‌ها</a>
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
                        <path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z" />
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
            background: linear-gradient(to right, rgba(255,255,255,0.3), rgba(255,255,255,0.8), rgba(255,255,255,0.3));
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
