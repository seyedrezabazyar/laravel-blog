@extends('layouts.blog-app')

@section('content')
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $category->name }}</h1>
                <p class="text-gray-600">{{ $posts->total() }} کتاب در این دسته‌بندی</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
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
                            <span class="mr-2 text-gray-700 font-medium">{{ $category->name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Category Description (optional) -->
        <div class="mb-10 bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-xl font-bold text-gray-800 mb-4">درباره دسته‌بندی {{ $category->name }}</h2>
            <p class="text-gray-600">
                در این بخش شما می‌توانید کتاب‌های مرتبط با {{ $category->name }} را مشاهده کنید.
                برای دسترسی سریع‌تر به محتوای مورد نظر خود می‌توانید از فیلترهای موجود استفاده کنید.
            </p>
        </div>

        <!-- Posts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($posts as $post)
                <x-blog-card :post="$post" />
            @empty
                <div class="col-span-3 bg-white p-12 rounded-lg shadow-sm text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ کتابی در این دسته‌بندی یافت نشد</h3>
                    <p class="text-gray-500">به زودی کتاب‌های جدیدی در این دسته‌بندی اضافه خواهد شد.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $posts->links() }}
        </div>

        <!-- Categories List -->
        <div class="mt-16 bg-gray-50 p-6 rounded-lg shadow-sm">
            <h3 class="text-xl font-bold text-gray-800 mb-6">همه دسته‌بندی‌ها</h3>
            <div class="flex flex-wrap gap-3">
                @foreach($allCategories as $cat)
                    <a href="{{ route('blog.category', $cat->slug) }}"
                       class="px-4 py-2 rounded-full text-sm {{ $cat->id == $category->id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-indigo-50' }}
                          transition shadow-sm">
                        {{ $cat->name }}
                        <span class="text-xs {{ $cat->id == $category->id ? 'text-indigo-200' : 'text-gray-500' }} ml-1">({{ $cat->posts_count }})</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
