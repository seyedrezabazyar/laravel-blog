<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('ایجاد کتاب جدید') }}
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
                    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- بخش اطلاعات اصلی کتاب -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">اطلاعات اصلی کتاب</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">عنوان فارسی</label>
                                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('title') border-red-300 @enderror"
                                           required>
                                    @error('title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="english_title" class="block text-sm font-medium text-gray-700 mb-1">عنوان انگلیسی</label>
                                    <input type="text" name="english_title" id="english_title" value="{{ old('english_title') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('english_title') border-red-300 @enderror">
                                    @error('english_title')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">دسته‌بندی</label>
                                    <select name="category_id" id="category_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('category_id') border-red-300 @enderror"
                                            required>
                                        <option value="">انتخاب دسته‌بندی</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                        <option value="فارسی" {{ old('language') == 'فارسی' ? 'selected' : '' }}>فارسی</option>
                                        <option value="انگلیسی" {{ old('language') == 'انگلیسی' ? 'selected' : '' }}>انگلیسی</option>
                                        <option value="عربی" {{ old('language') == 'عربی' ? 'selected' : '' }}>عربی</option>
                                        <option value="فرانسوی" {{ old('language') == 'فرانسوی' ? 'selected' : '' }}>فرانسوی</option>
                                        <option value="آلمانی" {{ old('language') == 'آلمانی' ? 'selected' : '' }}>آلمانی</option>
                                        <option value="سایر" {{ old('language') == 'سایر' ? 'selected' : '' }}>سایر</option>
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
                                            <option value="{{ $author->id }}" {{ old('author_id') == $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('author_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="co_authors" class="block text-sm font-medium text-gray-700 mb-1">نویسندگان همکار</label>
                                    <select name="co_authors[]" id="co_authors" multiple
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('co_authors') border-red-300 @enderror">
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}" {{ in_array($author->id, old('co_authors', [])) ? 'selected' : '' }}>{{ $author->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">برای انتخاب چند نویسنده، کلید Ctrl را نگه دارید.</p>
                                    @error('co_authors')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="publisher_id" class="block text-sm font-medium text-gray-700 mb-1">ناشر</label>
                                    <select name="publisher_id" id="publisher_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('publisher_id') border-red-300 @enderror">
                                        <option value="">انتخاب ناشر</option>
                                        @foreach($publishers as $publisher)
                                            <option value="{{ $publisher->id }}" {{ old('publisher_id') == $publisher->id ? 'selected' : '' }}>{{ $publisher->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('publisher_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="publication_year" class="block text-sm font-medium text-gray-700 mb-1">سال انتشار</label>
                                    <input type="number" name="publication_year" id="publication_year" value="{{ old('publication_year') }}" min="1800" max="{{ date('Y') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('publication_year') border-red-300 @enderror">
                                    @error('publication_year')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- بخش جزئیات کتاب -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">جزئیات کتاب</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <div>
                                    <label for="format" class="block text-sm font-medium text-gray-700 mb-1">فرمت کتاب</label>
                                    <select name="format" id="format"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('format') border-red-300 @enderror">
                                        <option value="">انتخاب فرمت</option>
                                        <option value="چاپی" {{ old('format') == 'چاپی' ? 'selected' : '' }}>چاپی</option>
                                        <option value="PDF" {{ old('format') == 'PDF' ? 'selected' : '' }}>PDF</option>
                                        <option value="EPUB" {{ old('format') == 'EPUB' ? 'selected' : '' }}>EPUB</option>
                                        <option value="MOBI" {{ old('format') == 'MOBI' ? 'selected' : '' }}>MOBI</option>
                                        <option value="صوتی" {{ old('format') == 'صوتی' ? 'selected' : '' }}>صوتی</option>
                                        <option value="سایر" {{ old('format') == 'سایر' ? 'selected' : '' }}>سایر</option>
                                    </select>
                                    @error('format')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="book_codes" class="block text-sm font-medium text-gray-700 mb-1">کدهای کتاب (ISBN و غیره)</label>
                                    <input type="text" name="book_codes" id="book_codes" value="{{ old('book_codes') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('book_codes') border-red-300 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">برای چندین کد، از علامت کاما استفاده کنید (مثلا: ISBN-10: 1234567890, ISBN-13: 978-1234567890)</p>
                                    @error('book_codes')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="purchase_link" class="block text-sm font-medium text-gray-700 mb-1">لینک خرید</label>
                                    <input type="url" name="purchase_link" id="purchase_link" value="{{ old('purchase_link') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('purchase_link') border-red-300 @enderror">
                                    @error('purchase_link')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">برچسب‌ها</label>
                                    <input type="text" name="tags" id="tags" value="{{ old('tags') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('tags') border-red-300 @enderror">
                                    <p class="text-xs text-gray-500 mt-1">برچسب‌ها را با کاما از هم جدا کنید.</p>
                                    @error('tags')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- بخش محتوا -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">محتوای کتاب</h3>

                            <div class="mb-4">
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">توضیحات فارسی</label>
                                <textarea name="content" id="content" rows="10"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('content') border-red-300 @enderror"
                                          required>{{ old('content') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">از تگ‌های HTML برای قالب‌بندی متن استفاده کنید.</p>
                                @error('content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="english_content" class="block text-sm font-medium text-gray-700 mb-1">توضیحات انگلیسی (اختیاری)</label>
                                <textarea name="english_content" id="english_content" rows="10"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('english_content') border-red-300 @enderror">{{ old('english_content') }}</textarea>
                                @error('english_content')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- بخش تصاویر -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">تصاویر کتاب</h3>

                            <div class="mb-4">
                                <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1">تصویر اصلی (جلد کتاب)</label>
                                <input type="file" name="featured_image" id="featured_image"
                                       class="mt-1 block w-full border border-gray-300 rounded-md p-2 @error('featured_image') border-red-300 @enderror"
                                       accept="image/*">
                                <p class="mt-1 text-xs text-gray-500">تصویر مناسب با اندازه حداقل 600×900 پیکسل انتخاب کنید.</p>

                                <div class="mt-2 flex items-center">
                                    <input type="checkbox" name="hide_image" id="hide_image" value="1" {{ old('hide_image') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ml-2">
                                    <label for="hide_image" class="text-sm text-gray-700">مخفی کردن تصویر اصلی</label>
                                </div>

                                @error('featured_image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">تصاویر اضافی</label>
                                <div class="border border-gray-300 rounded-md p-4">
                                    <div id="additional-images">
                                        <div class="additional-image mb-3 pb-3 border-b border-gray-200">
                                            <input type="file" name="post_images[]" class="mt-1 block w-full border border-gray-300 rounded-md p-2" accept="image/*">
                                            <div class="grid grid-cols-2 gap-2 mt-2">
                                                <div>
                                                    <label class="text-sm text-gray-700">عنوان تصویر (اختیاری)</label>
                                                    <input type="text" name="image_captions[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                                                </div>
                                                <div class="flex items-center mt-5">
                                                    <input type="checkbox" name="hide_post_images[]" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ml-2">
                                                    <label class="text-sm text-gray-700">مخفی کردن این تصویر</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" id="add-image-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-1 px-3 rounded text-sm mt-2">
                                        افزودن تصویر دیگر
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- گزینه‌های انتشار -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">گزینه‌های انتشار</h3>

                            <div class="flex flex-col space-y-3">
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ml-2">
                                    <label for="is_published" class="text-sm text-gray-700">منتشر شود</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="hide_content" id="hide_content" value="1" {{ old('hide_content') ? 'checked' : '' }}
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
                                    ذخیره کتاب
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const addImageBtn = document.getElementById('add-image-btn');
                const additionalImagesContainer = document.getElementById('additional-images');

                addImageBtn.addEventListener('click', function() {
                    const newImageDiv = document.createElement('div');
                    newImageDiv.className = 'additional-image mb-3 pb-3 border-b border-gray-200';
                    newImageDiv.innerHTML = `
                    <input type="file" name="post_images[]" class="mt-1 block w-full border border-gray-300 rounded-md p-2" accept="image/*">
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <div>
                            <label class="text-sm text-gray-700">عنوان تصویر (اختیاری)</label>
                            <input type="text" name="image_captions[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                        </div>
                        <div class="flex items-center mt-5">
                            <input type="checkbox" name="hide_post_images[]" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ml-2">
                            <label class="text-sm text-gray-700">مخفی کردن این تصویر</label>
                        </div>
                    </div>
                    <button type="button" class="remove-image-btn bg-red-100 hover:bg-red-200 text-red-800 py-1 px-3 rounded text-sm mt-2">
                        حذف این تصویر
                    </button>
                `;

                    additionalImagesContainer.appendChild(newImageDiv);

                    // اضافه کردن عملکرد دکمه حذف
                    const removeBtn = newImageDiv.querySelector('.remove-image-btn');
                    removeBtn.addEventListener('click', function() {
                        newImageDiv.remove();
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
