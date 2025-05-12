<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('داشبورد') }}
            </h2>
            <a href="{{ route('blog.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 9h1v7a1 1 0 001 1h4a1 1 0 001-1v-4h2v4a1 1 0 001 1h4a1 1 0 001-1V9h1a1 1 0 00.707-1.707l-7-7z" />
                </svg>
                مشاهده سایت
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- نوار وضعیت با حداقل اطلاعات -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4 border-r-4 border-blue-500">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-blue-600">وضعیت انتشار:</span>
                        <span class="{{ $post->is_published ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ $post->is_published ? 'منتشر شده' : 'پیش‌نویس' }}
                        </span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-600">وضعیت محتوا:</span>
                        <span class="{{ $post->hide_content ? 'text-red-600' : 'text-green-600' }}">
                            {{ $post->hide_content ? 'مخفی' : 'قابل نمایش' }}
                        </span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-600">وضعیت تصویر:</span>
                        <span class="{{ isset($featuredImage->hide_image) && $featuredImage->hide_image == 'hidden' ? 'text-red-600' : 'text-green-600' }}">
                            {{ isset($featuredImage->hide_image) && $featuredImage->hide_image == 'hidden' ? 'مخفی' : 'قابل نمایش' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- فرم ویرایش با ساختار سبک‌تر -->
            <form action="{{ route('admin.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- کارت اطلاعات اصلی -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">اطلاعات اصلی کتاب</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- ستون راست -->
                            <div class="space-y-4">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">عنوان کتاب (فارسی) <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                                </div>

                                <div>
                                    <label for="english_title" class="block text-sm font-medium text-gray-700 mb-1">عنوان کتاب (انگلیسی)</label>
                                    <input type="text" name="english_title" id="english_title" value="{{ old('english_title', $post->english_title ?? '') }}"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">دسته‌بندی <span class="text-red-500">*</span></label>
                                    <select name="category_id" id="category_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ $post->category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- ستون چپ -->
                            <div class="space-y-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label for="publication_year" class="block text-sm font-medium text-gray-700 mb-1">سال انتشار</label>
                                        <input type="number" name="publication_year" id="publication_year"
                                               value="{{ old('publication_year', $post->publication_year ?? '') }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    </div>

                                    <div>
                                        <label for="language" class="block text-sm font-medium text-gray-700 mb-1">زبان کتاب</label>
                                        <input type="text" name="language" id="language"
                                               value="{{ old('language', $post->language ?? '') }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    </div>

                                    <div>
                                        <label for="format" class="block text-sm font-medium text-gray-700 mb-1">فرمت کتاب</label>
                                        <input type="text" name="format" id="format"
                                               value="{{ old('format', $post->format ?? '') }}"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    </div>
                                </div>

                                <div>
                                    <label for="book_codes" class="block text-sm font-medium text-gray-700 mb-1">کد کتاب (شابک)</label>
                                    <input type="text" name="book_codes" id="book_codes"
                                           value="{{ old('book_codes', $post->book_codes ?? '') }}"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label for="purchase_link" class="block text-sm font-medium text-gray-700 mb-1">لینک خرید</label>
                                    <input type="url" name="purchase_link" id="purchase_link"
                                           value="{{ old('purchase_link', $post->purchase_link ?? '') }}"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>
                            </div>
                        </div>

                        <!-- وضعیت انتشار -->
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <div class="flex flex-wrap gap-6">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="is_published" value="1" {{ $post->is_published ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm ml-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    منتشر شود
                                </label>

                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="hide_content" value="1" {{ $post->hide_content ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm ml-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    مخفی کردن محتوا
                                </label>

                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="hide_image" value="1"
                                           {{ isset($featuredImage->hide_image) && $featuredImage->hide_image == 'hidden' ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm ml-2 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    مخفی کردن تصویر
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- کارت تصویر -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">تصویر جلد کتاب</h3>

                        <div class="flex items-start space-x-4 space-x-reverse">
                            <!-- نمایش تصویر فعلی -->
                            <div class="w-28 h-40 flex-shrink-0">
                                @if(isset($featuredImage) && $featuredImage && $featuredImage->image_path)
                                    <div class="relative w-full h-full">
                                        <img
                                            src="{{ asset('storage/' . $featuredImage->image_path) }}"
                                            alt="{{ $post->title }}"
                                            class="object-cover w-full h-full rounded-md border"
                                            onerror="this.src='{{ asset('images/default-book.png') }}';"
                                        >

                                        @if(isset($featuredImage->hide_image) && $featuredImage->hide_image == 'hidden')
                                            <div class="absolute inset-0 bg-red-500 bg-opacity-30 flex items-center justify-center rounded-md">
                                                <span class="text-white text-xs font-bold">مخفی</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded-md border">
                                        <span class="text-xs text-gray-400">بدون تصویر</span>
                                    </div>
                                @endif
                            </div>

                            <!-- آپلود تصویر جدید -->
                            <div class="flex-grow">
                                <div class="mb-2">
                                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">تصویر جدید</label>
                                    <input type="file" name="image" id="image"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                           accept="image/*">
                                </div>
                                <p class="text-xs text-gray-500">فرمت‌های مجاز: JPG، PNG - حداکثر 2 مگابایت</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- کارت محتوا -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 md:p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">محتوای کتاب</h3>

                        <div class="space-y-4">
                            <!-- محتوای فارسی -->
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">معرفی کتاب (فارسی) <span class="text-red-500">*</span></label>
                                <textarea name="content" id="content" rows="8"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                          required
                                          x-data="{ resize: () => { $el.style.height = '200px'; $el.style.height = $el.scrollHeight + 'px' } }"
                                          x-init="resize()"
                                          x-on:input="resize()"
                                >{{ old('content', $post->content) }}</textarea>
                            </div>

                            <!-- محتوای انگلیسی -->
                            <div>
                                <label for="english_content" class="block text-sm font-medium text-gray-700 mb-1">معرفی کتاب (انگلیسی)</label>
                                <textarea name="english_content" id="english_content" rows="8"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                          dir="ltr"
                                          x-data="{ resize: () => { $el.style.height = '200px'; $el.style.height = $el.scrollHeight + 'px' } }"
                                          x-init="resize()"
                                          x-on:input="resize()"
                                >{{ old('english_content', $post->english_content ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- دکمه‌های ثبت -->
                <div class="flex justify-between items-center pt-2">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition shadow">
                        ذخیره تغییرات
                    </button>

                    <div class="flex space-x-2 space-x-reverse">
                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            مشاهده در وبلاگ
                        </a>

                        <a href="{{ route('admin.posts.index') }}"
                           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                            انصراف
                        </a>
                    </div>
                </div>

                <!-- فیلدهای مخفی برای نویسنده، ناشر و تگ -->
                <input type="hidden" name="author_id" value="{{ $post->author_id }}">
                <input type="hidden" name="publisher_id" value="{{ $post->publisher_id }}">
                @if(isset($tags_list))
                    <input type="hidden" name="tags" value="{{ $tags_list }}">
                @endif
            </form>
        </div>
    </div>
</x-app-layout>
