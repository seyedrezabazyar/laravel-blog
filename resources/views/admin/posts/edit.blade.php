<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('ویرایش کتاب') }}: {{ $post->title }}
            </h2>
            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                بازگشت به لیست کتاب‌ها
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('success'))
                        <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-6 rounded-md flex items-center">
                            <svg class="h-6 w-6 ml-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded-md flex items-center">
                            <svg class="h-6 w-6 ml-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.posts.update', $post) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- بخش اطلاعات اصلی کتاب -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">اطلاعات اصلی کتاب</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">عنوان فارسی</label>
                                    <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('title') border-red-300 @enderror"
                                           required>
                                    @error('title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="english_title" class="block text-sm font-medium text-gray-700 mb-1">عنوان انگلیسی</label>
                                    <input type="text" name="english_title" id="english_title" value="{{ old('english_title', $post->english_title) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('english_title') border-red-300 @enderror">
                                    @error('english_title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">دسته‌بندی</label>
                                    <select name="category_id" id="category_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('category_id') border-red-300 @enderror"
                                            required>
                                        <option value="">انتخاب دسته‌بندی</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="language" class="block text-sm font-medium text-gray-700 mb-1">زبان کتاب</label>
                                    <select name="language" id="language"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('language') border-red-300 @enderror">
                                        <option value="">انتخاب زبان</option>
                                        <option value="فارسی" {{ old('language', $post->language) == 'فارسی' ? 'selected' : '' }}>فارسی</option>
                                        <option value="انگلیسی" {{ old('language', $post->language) == 'انگلیسی' ? 'selected' : '' }}>انگلیسی</option>
                                        <option value="عربی" {{ old('language', $post->language) == 'عربی' ? 'selected' : '' }}>عربی</option>
                                        <option value="فرانسوی" {{ old('language', $post->language) == 'فرانسوی' ? 'selected' : '' }}>فرانسوی</option>
                                        <option value="آلمانی" {{ old('language', $post->language) == 'آلمانی' ? 'selected' : '' }}>آلمانی</option>
                                        <option value="سایر" {{ old('language', $post->language) == 'سایر' ? 'selected' : '' }}>سایر</option>
                                    </select>
                                    @error('language')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- بخش نویسندگان و ناشر -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">نویسندگان و ناشر</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="author_id" class="block text-sm font-medium text-gray-700 mb-1">نویسنده اصلی</label>
                                    <select name="author_id" id="author_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('author_id') border-red-300 @enderror">
                                        <option value="">انتخاب نویسنده اصلی</option>
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}" {{ old('author_id', $post->author_id) == $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('author_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="publisher_id" class="block text-sm font-medium text-gray-700 mb-1">ناشر</label>
                                    <select name="publisher_id" id="publisher_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('publisher_id') border-red-300 @enderror">
                                        <option value="">انتخاب ناشر</option>
                                        @foreach($publishers as $publisher)
                                            <option value="{{ $publisher->id }}" {{ old('publisher_id', $post->publisher_id) == $publisher->id ? 'selected' : '' }}>{{ $publisher->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('publisher_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- اطلاعات اضافی کتاب -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">اطلاعات تکمیلی</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <label for="publication_year" class="block text-sm font-medium text-gray-700 mb-1">سال انتشار</label>
                                    <input type="number" name="publication_year" id="publication_year" value="{{ old('publication_year', $post->publication_year) }}" min="1800" max="{{ date('Y') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('publication_year') border-red-300 @enderror">
                                    @error('publication_year')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="format" class="block text-sm font-medium text-gray-700 mb-1">فرمت کتاب</label>
                                    <select name="format" id="format"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('format') border-red-300 @enderror">
                                        <option value="">انتخاب فرمت</option>
                                        <option value="چاپی" {{ old('format', $post->format) == 'چاپی' ? 'selected' : '' }}>چاپی</option>
                                        <option value="PDF" {{ old('format', $post->format) == 'PDF' ? 'selected' : '' }}>PDF</option>
                                        <option value="EPUB" {{ old('format', $post->format) == 'EPUB' ? 'selected' : '' }}>EPUB</option>
                                        <option value="MOBI" {{ old('format', $post->format) == 'MOBI' ? 'selected' : '' }}>MOBI</option>
                                        <option value="صوتی" {{ old('format', $post->format) == 'صوتی' ? 'selected' : '' }}>صوتی</option>
                                        <option value="سایر" {{ old('format', $post->format) == 'سایر' ? 'selected' : '' }}>سایر</option>
                                    </select>
                                    @error('format')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <label for="book_codes" class="block text-sm font-medium text-gray-700 mb-1">کدهای کتاب (ISBN و غیره)</label>
                                    <input type="text" name="book_codes" id="book_codes" value="{{ old('book_codes', $post->book_codes) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('book_codes') border-red-300 @enderror">
                                    @error('book_codes')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="purchase_link" class="block text-sm font-medium text-gray-700 mb-1">لینک خرید</label>
                                    <input type="url" name="purchase_link" id="purchase_link" value="{{ old('purchase_link', $post->purchase_link) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('purchase_link') border-red-300 @enderror">
                                    @error('purchase_link')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- نمایش اطلاعات تصویر (بدون امکان تغییر) -->
                            @if($post->featuredImage)
                                <div class="bg-blue-50 p-3 rounded-md mt-3 text-gray-700 text-sm">
                                    <p><strong>وضعیت تصویر:</strong> این کتاب دارای تصویر است</p>
                                    <p class="mt-1"><strong>مسیر تصویر:</strong> <code class="bg-gray-100 px-1 py-0.5 rounded text-xs text-gray-700">{{ $post->featuredImage->image_path }}</code></p>
                                    <p class="mt-1"><strong>وضعیت نمایش:</strong> {{ $post->featuredImage->hide_image === 'hidden' ? 'مخفی' : 'قابل نمایش' }}</p>
                                    <input type="hidden" name="hide_image" value="{{ $post->featuredImage->hide_image === 'hidden' ? '1' : '0' }}">
                                </div>
                            @else
                                <div class="bg-yellow-50 p-3 rounded-md mt-3 text-gray-700 text-sm">
                                    <p><strong>وضعیت تصویر:</strong> این کتاب بدون تصویر است</p>
                                </div>
                            @endif
                        </div>

                        <!-- بخش محتوا -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">محتوای کتاب</h3>

                            <div class="mb-4">
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">توضیحات فارسی</label>
                                <textarea name="content" id="content" rows="10"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('content') border-red-300 @enderror"
                                          required>{{ old('content', $post->content) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">از تگ‌های HTML برای قالب‌بندی متن استفاده کنید.</p>
                                @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="english_content" class="block text-sm font-medium text-gray-700 mb-1">توضیحات انگلیسی (اختیاری)</label>
                                <textarea name="english_content" id="english_content" rows="8"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('english_content') border-red-300 @enderror">{{ old('english_content', $post->english_content) }}</textarea>
                                @error('english_content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- گزینه‌های انتشار -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">گزینه‌های انتشار</h3>

                            <div class="flex flex-col space-y-3">
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $post->is_published) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ml-2">
                                    <label for="is_published" class="text-sm text-gray-700">منتشر شود</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="hide_content" id="hide_content" value="1" {{ old('hide_content', $post->hide_content) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ml-2">
                                    <label for="hide_content" class="text-sm text-gray-700">محتوا مخفی باشد (فقط برای کاربران خاص)</label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    بروزرسانی کتاب
                                </div>
                            </button>
                            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                                انصراف
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
