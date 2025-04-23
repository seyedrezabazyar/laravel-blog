@extends('layouts.blog-app')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-indigo-50 to-purple-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 fade-in">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">اکتشاف دنیای کتاب‌ها</h1>
                    <p class="text-xl text-gray-600 mb-8">با کتابستان، دنیایی از دانش و الهام را کشف کنید. ما برترین کتاب‌ها را به شما معرفی می‌کنیم.</p>
                    <div class="flex space-x-4 space-x-reverse">
                        <a href="#latest-posts" class="bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg text-lg font-medium transition duration-150">جدیدترین کتاب‌ها</a>
                        <a href="{{ route('blog.categories') }}" class="bg-white hover:bg-gray-100 text-indigo-600 py-3 px-6 rounded-lg text-lg font-medium transition duration-150 border border-indigo-200">دسته‌بندی‌ها</a>
                    </div>
                </div>
                <div class="md:w-1/2 mt-10 md:mt-0 fade-in" style="animation-delay: 0.3s;">
                    <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="کتابخانه" class="rounded-lg shadow-xl">
                </div>
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

            <div class="mt-10">
                {{ $posts->links() }}
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
@endsection
