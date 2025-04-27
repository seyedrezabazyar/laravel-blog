<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('مشاهده کتاب') }}
            </h2>
            <div class="flex space-x-2 space-x-reverse">
                <a href="{{ route('admin.posts.edit', $post) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    ویرایش
                </a>
                <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    بازگشت
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- اطلاعات اصلی کتاب -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- تصویر کتاب -->
                        <div class="md:col-span-1">
                            @if($post->featured_image && !$post->hide_image)
                                <div class="max-w-xs mx-auto">
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-auto object-cover rounded-lg shadow-lg">
                                </div>
                            @else
                                <div class="max-w-xs mx-auto h-64 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-lg shadow-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            @endif

                            <div class="mt-4 bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold mb-2">وضعیت انتشار</h3>
                                <div class="space-y-1 text-sm">
                                    <div class="flex items-center">
                                        <span class="w-32 font-medium">وضعیت:</span>
                                        <span class="{{ $post->is_published ? 'text-green-600' : 'text-yellow-600' }}">
                                            {{ $post->is_published ? 'منتشر شده' : 'پیش‌نویس' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-32 font-medium">محتوا:</span>
                                        <span class="{{ $post->hide_content ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $post->hide_content ? 'مخفی' : 'قابل نمایش' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-32 font-medium">تصویر:</span>
                                        <span class="{{ $post->hide_image ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $post->hide_image ? 'مخفی' : 'قابل نمایش' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-32 font-medium">تاریخ ایجاد:</span>
                                        <span class="text-gray-600">{{ $post->created_at->format('Y/m/d H:i') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-32 font-medium">آخرین بروزرسانی:</span>
                                        <span class="text-gray-600">{{ $post->updated_at->format('Y/m/d H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- اطلاعات کتاب -->
                        <div class="md:col-span-2">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $post->title }}</h1>
                            @if($post->english_title)
                                <h2 class="text-xl text-gray-700 mb-4">{{ $post->english_title }}</h2>
                            @endif

                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <h3 class="text-lg font-semibold mb-2">اطلاعات کتاب</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-1 text-sm">
                                        <div class="flex items-center">
                                            <span class="w-24 font-medium">دسته‌بندی:</span>
                                            <a href="{{ route('blog.category', $post->category->slug) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $post->category->name }}
                                            </a>
                                        </div>

                                        <div class="flex items-center">
                                            <span class="w-24 font-medium">نویسنده اصلی:</span>
                                            @if($post->author)
                                                <a href="{{ route('admin.authors.show', $post->author) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $post->author->name }}
                                                </a>
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </div>

                                        <div class="flex items-center">
                                            <span class="w-24 font-medium">ناشر:</span>
                                            @if($post->publisher)
                                                <a href="{{ route('admin.publishers.show', $post->publisher) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $post->publisher->name }}
                                                </a>
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </div>

                                        <div class="flex items-center">
                                            <span class="w-24 font-medium">زبان کتاب:</span>
                                            <span class="text-gray-600">{{ $post->language ?? '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="space-y-1 text-sm">
                                        <div class="flex items-center">
                                            <span class="w-24 font-medium">سال انتشار:</span>
                                            <span class="text-gray-600">{{ $post->publication_year ?? '-' }}</span>
                                        </div>

                                        <div class="flex items-center">
                                            <span class="w-24 font-medium">فرمت:</span>
                                            <span class="text-gray-600">{{ $post->format ?? '-' }}</span>
                                        </div>

                                        <div class="flex items-center">
                                            <span class="w-24 font-medium">کدهای کتاب:</span>
                                            <span class="text-gray-600">{{ $post->book_codes ?? '-' }}</span>
                                        </div>

                                        @if($post->purchase_link)
                                            <div class="flex items-center">
                                                <span class="w-24 font-medium">لینک خرید:</span>
                                                <a href="{{ $post->purchase_link }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 truncate max-w-xs">
                                                    {{ $post->purchase_link }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($post->authors->count() > 0)
                                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                    <h3 class="text-lg font-semibold mb-2">نویسندگان همکار</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($post->authors as $coAuthor)
                                            <a href="{{ route('admin.authors.show', $coAuthor) }}" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm hover:bg-indigo-200 transition">
                                                {{ $coAuthor->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($post->tags && $post->tags->count() > 0)
                                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                    <h3 class="text-lg font-semibold mb-2">برچسب‌های کتاب</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($post->tags as $tag)
                                            <a href="{{ route('blog.tag', $tag->slug) }}" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition">
                                                {{ $tag->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- محتوای کتاب -->
                    <div class="border-t border-gray-200 pt-8 mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">توضیحات کتاب</h2>

                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">توضیحات فارسی</h3>
                            <div class="prose prose-lg max-w-none rtl">
                                {!! $post->purified_content !!}
                            </div>
                        </div>

                        @if($post->english_content)
                            <div class="bg-gray-50 p-6 rounded-lg mb-6">
                                <h3 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">توضیحات انگلیسی</h3>
                                <div class="prose prose-lg max-w-none ltr">
                                    {!! $post->english_content !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- تصاویر اضافی -->
                    @if($post->images->count() > 0)
                        <div class="border-t border-gray-200 pt-8 mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">تصاویر کتاب</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($post->images as $image)
                                    <div class="bg-white shadow rounded-lg overflow-hidden {{ $image->hide_image ? 'opacity-50' : '' }}">
                                        <img src="{{ $image->image_url }}" alt="{{ $image->caption ?? $post->title }}" class="w-full h-48 object-cover">
                                        <div class="p-4">
                                            @if($image->caption)
                                                <p class="text-gray-700 text-sm">{{ $image->caption }}</p>
                                            @endif
                                            <div class="flex justify-between items-center mt-2">
                                                <span class="text-xs text-gray-500">تصویر {{ $loop->iteration }}</span>
                                                @if($image->hide_image)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">مخفی</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-wrap justify-between items-center mt-8 pt-6 border-t border-gray-200">
                        <div>
                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                                </svg>
                                مشاهده در وبلاگ
                            </a>
                        </div>
                        <div class="flex space-x-2 space-x-reverse">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150">
                                ویرایش
                            </a>
                            <form class="inline-block" action="{{ route('admin.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('آیا از حذف این کتاب اطمینان دارید؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-150">
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
