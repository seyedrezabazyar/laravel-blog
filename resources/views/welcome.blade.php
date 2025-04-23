{{-- resources/views/welcome.blade.php --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>کتابستان | دنیای کتاب و کتابخوانی</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }

        /* Hero section animation */
        .fade-in {
            animation: fadeIn 1.5s ease-in-out;
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Book hover effect */
        .book-card {
            transition: all 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Quote animation */
        .quote-animation {
            animation: fadeInUp 2s ease;
        }

        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">
<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center">
                    <span class="text-2xl font-bold text-indigo-600">کتابستان</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex space-x-8 space-x-reverse">
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-150">وبلاگ</a>
                <a href="#categories" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-150">دسته‌بندی‌ها</a>
                <a href="#recommended" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-150">کتاب‌های پیشنهادی</a>
                <a href="#about" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-150">درباره ما</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="flex items-center space-x-4 space-x-reverse">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800 transition">داشبورد</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md transition">خروج</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 transition">ورود</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md transition">ثبت نام</a>
                    @endif
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none" id="mobile-menu-button">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ route('blog.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition">وبلاگ</a>
                <a href="#categories" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition">دسته‌بندی‌ها</a>
                <a href="#recommended" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition">کتاب‌های پیشنهادی</a>
                <a href="#about" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition">درباره ما</a>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-indigo-50 to-purple-50 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 fade-in">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">اکتشاف دنیای کتاب‌ها</h1>
                <p class="text-xl text-gray-600 mb-8">با کتابستان، دنیایی از دانش و الهام را کشف کنید. ما برترین کتاب‌ها را به شما معرفی می‌کنیم.</p>
                <div class="flex space-x-4 space-x-reverse">
                    <a href="{{ route('blog.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg text-lg font-medium transition duration-150">مطالب وبلاگ</a>
                    <a href="#recommended" class="bg-white hover:bg-gray-100 text-indigo-600 py-3 px-6 rounded-lg text-lg font-medium transition duration-150 border border-indigo-200">کتاب‌های پیشنهادی</a>
                </div>
            </div>
            <div class="md:w-1/2 mt-10 md:mt-0 fade-in" style="animation-delay: 0.3s;">
                <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="کتابخانه" class="rounded-lg shadow-xl">
            </div>
        </div>
    </div>
</section>

<!-- Featured Posts -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">آخرین مطالب وبلاگ</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach(\App\Models\Post::with(['category', 'user'])->where('is_published', true)->latest()->take(3)->get() as $post)
                <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
                    @if($post->featured_image)
                        <div class="h-48 overflow-hidden">
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
                        </div>
                    @else
                        <div class="h-48 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                            <svg class="w-16 h-16 text-indigo-300" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    @endif
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 mb-2">
                            <span>{{ $post->created_at->format('Y/m/d') }}</span>
                            <span class="mx-2">•</span>
                            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-indigo-600 hover:text-indigo-800">{{ $post->category->name }}</a>
                        </div>
                        <h3 class="text-xl font-bold mb-3">{{ $post->title }}</h3>
                        <p class="text-gray-600 mb-4">{{ Str::limit(strip_tags($post->content), 120) }}</p>
                        <div class="flex items-center justify-between">
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">ادامه مطلب</a>
                            <span class="text-sm text-gray-500">{{ $post->user->name }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('blog.index') }}" class="inline-block bg-white hover:bg-gray-50 text-indigo-600 py-3 px-8 rounded-lg text-lg font-medium transition border border-indigo-200">مشاهده همه مطالب</a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section id="categories" class="py-16 bg-gradient-to-r from-indigo-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">دسته‌بندی‌های کتاب</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach(\App\Models\Category::withCount('posts')->get() as $category)
                <a href="{{ route('blog.category', $category->slug) }}" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center">
                    <div class="bg-indigo-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $category->name }}</h3>
                    <p class="text-gray-500">{{ $category->posts_count }} مطلب</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Quote Section -->
<section class="py-20 bg-indigo-700 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center quote-animation">
        <svg class="w-12 h-12 mx-auto mb-6 text-indigo-300" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z" />
        </svg>
        <blockquote class="text-xl md:text-2xl font-medium mb-8">
            کتاب‌ها تنها اشیایی هستند که می‌توانید امروز خریداری کنید و تا آخر عمر از آن‌ها لذت ببرید.
        </blockquote>
        <p class="text-lg text-indigo-200">— وارن بافت</p>
    </div>
</section>

<!-- Recommended Books -->
<section id="recommended" class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">کتاب‌های پیشنهادی</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Book 1 -->
            <div class="book-card bg-white rounded-lg overflow-hidden shadow-md">
                <div class="h-56 bg-indigo-100 flex items-center justify-center p-4">
                    <img src="https://images-na.ssl-images-amazon.com/images/I/51Ga5GuElyL._AC_SX184_.jpg" alt="کتاب" class="max-h-full">
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-2">هنر ظریف بی‌خیالی</h3>
                    <p class="text-gray-600 mb-4 text-sm">مارک منسون</p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <span class="text-gray-700">4.5</span>
                        </div>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">بیشتر</a>
                    </div>
                </div>
            </div>

            <!-- Book 2 -->
            <div class="book-card bg-white rounded-lg overflow-hidden shadow-md">
                <div class="h-56 bg-indigo-100 flex items-center justify-center p-4">
                    <img src="https://images-na.ssl-images-amazon.com/images/I/41r6F2LRf8L._AC_SX184_.jpg" alt="کتاب" class="max-h-full">
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-2">عادت‌های اتمی</h3>
                    <p class="text-gray-600 mb-4 text-sm">جیمز کلیر</p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <span class="text-gray-700">4.8</span>
                        </div>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">بیشتر</a>
                    </div>
                </div>
            </div>

            <!-- Book 3 -->
            <div class="book-card bg-white rounded-lg overflow-hidden shadow-md">
                <div class="h-56 bg-indigo-100 flex items-center justify-center p-4">
                    <img src="https://images-na.ssl-images-amazon.com/images/I/51-uspgqWIL._AC_SX184_.jpg" alt="کتاب" class="max-h-full">
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-2">اثر مرکب</h3>
                    <p class="text-gray-600 mb-4 text-sm">دارن هاردی</p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <span class="text-gray-700">4.7</span>
                        </div>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">بیشتر</a>
                    </div>
                </div>
            </div>

            <!-- Book 4 -->
            <div class="book-card bg-white rounded-lg overflow-hidden shadow-md">
                <div class="h-56 bg-indigo-100 flex items-center justify-center p-4">
                    <img src="https://images-na.ssl-images-amazon.com/images/I/41NzgvuL1ZL._AC_SX184_.jpg" alt="کتاب" class="max-h-full">
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-2">کیمیاگر</h3>
                    <p class="text-gray-600 mb-4 text-sm">پائولو کوئلیو</p>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <span class="text-gray-700">4.6</span>
                        </div>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">بیشتر</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="py-16 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-3">همراه ما باشید</h2>
        <p class="text-lg text-gray-600 mb-8">برای دریافت پیشنهادات کتاب و مطالب جدید در خبرنامه ما عضو شوید.</p>
        <form class="flex flex-col md:flex-row max-w-lg mx-auto">
            <input type="email" placeholder="ایمیل شما" class="w-full px-4 py-3 mb-2 md:mb-0 md:mr-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg transition">عضویت</button>
        </form>
    </div>
</section>

<!-- About Us -->
<section id="about" class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 pr-0 md:pr-10 mb-10 md:mb-0">
                <h2 class="text-3xl font-bold mb-6">درباره کتابستان</h2>
                <p class="text-gray-600 mb-4">کتابستان یک وبلاگ تخصصی در زمینه معرفی و نقد کتاب است. ما معتقدیم کتاب‌ها می‌توانند زندگی افراد را تغییر دهند و به همین دلیل تلاش می‌کنیم بهترین کتاب‌ها را به شما معرفی کنیم.</p>
                <p class="text-gray-600 mb-6">در کتابستان، علاوه بر معرفی کتاب‌های جدید، مقالات آموزشی درباره مطالعه مؤثر، خلاصه کتاب‌ها و مصاحبه با نویسندگان را نیز منتشر می‌کنیم.</p>
                <div class="flex space-x-4 space-x-reverse">
                    <a href="#" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-600 p-2 rounded-full transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"></path></svg>
                    </a>
                    <a href="#" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-600 p-2 rounded-full transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path></svg>
                    </a>
                    <a href="#" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-600 p-2 rounded-full transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"></path></svg>
                    </a>
                </div>
            </div>
            <div class="md:w-1/2">
                <img src="https://images.unsplash.com/photo-1513001900722-370f803f498d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="کتابخانه" class="rounded-lg shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-16 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">آماده غرق شدن در دنیای کتاب‌ها هستید؟</h2>
        <p class="text-xl text-indigo-100 mb-8">با ما همراه شوید و از مطالب جذاب و معرفی بهترین کتاب‌ها لذت ببرید.</p>
        <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4 sm:space-x-reverse">
            <a href="{{ route('blog.index') }}" class="bg-white text-indigo-700 hover:bg-indigo-50 py-3 px-8 rounded-lg font-medium transition">مطالب وبلاگ</a>
            {{--            <a href="{{ route('register') }}" class="border-2 border-white text-white hover:bg-white hover:text-indigo-700 py-3 px-8 rounded-lg font-medium transition">ثبت‌نام</a>--}}
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-800 text-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">کتابستان</h3>
                <p class="mb-4 text-gray-400">دنیایی از کتاب و دانش را با ما تجربه کنید.</p>
                <div class="flex space-x-4 space-x-reverse">
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"></path></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"></path></svg>
                    </a>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">دسترسی سریع</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('blog.index') }}" class="text-gray-400 hover:text-white transition">وبلاگ</a></li>
                    <li><a href="#categories" class="text-gray-400 hover:text-white transition">دسته‌بندی‌ها</a></li>
                    <li><a href="#recommended" class="text-gray-400 hover:text-white transition">کتاب‌های پیشنهادی</a></li>
                    <li><a href="#about" class="text-gray-400 hover:text-white transition">درباره ما</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">اطلاعات</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white transition">قوانین و مقررات</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">حریم خصوصی</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">تماس با ما</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">همکاری با ما</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4">خبرنامه</h3>
                <p class="text-gray-400 mb-4">برای اطلاع از آخرین مطالب و معرفی کتاب‌های جدید، در خبرنامه ما عضو شوید.</p>
                <form class="flex">
                    <input type="email" placeholder="ایمیل شما" class="bg-gray-700 border border-gray-600 text-gray-300 px-4 py-2 rounded-r-none rounded-l-lg w-full focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-l-none rounded-r-lg transition">ثبت</button>
                </form>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-10 pt-6 text-center text-gray-400">
            <p>&copy; ۱۴۰۴ کتابستان - تمامی حقوق محفوظ است.</p>
        </div>
    </div>
</footer>

<!-- JavaScript for Mobile Menu Toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });

        // Close mobile menu when clicking on a menu item
        const mobileMenuItems = document.querySelectorAll('#mobile-menu a');
        mobileMenuItems.forEach(item => {
            item.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
            });
        });
    });
</script>
</body>
</html>
