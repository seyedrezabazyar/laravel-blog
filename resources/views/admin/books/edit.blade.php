<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ویرایش کتاب') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.books.update', $book) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- عنوان فارسی -->
                            <div>
                                <label for="title_fa" class="block text-sm font-medium text-gray-700">عنوان فارسی</label>
                                <input type="text" name="title_fa" id="title_fa" value="{{ old('title_fa', $book->title_fa) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @error('title_fa')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- عنوان انگلیسی -->
                            <div>
                                <label for="title_en" class="block text-sm font-medium text-gray-700">عنوان انگلیسی</label>
                                <input type="text" name="title_en" id="title_en" value="{{ old('title_en', $book->title_en) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('title_en')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- دسته‌بندی -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">دسته‌بندی</label>
                                <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">انتخاب دسته‌بندی</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- نویسندگان -->
                            <div>
                                <label for="authors" class="block text-sm font-medium text-gray-700">نویسندگان</label>
                                <select name="authors[]" id="authors" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" multiple>
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}" {{ in_array($author->id, old('authors', $book->authors->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $author->name }}</option>
                                    @endforeach
                                </select>
                                @error('authors')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- لینک خرید -->
                            <div>
                                <label for="purchase_link" class="block text-sm font-medium text-gray-700">لینک خرید</label>
                                <input type="url" name="purchase_link" id="purchase_link" value="{{ old('purchase_link', $book->purchase_link) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('purchase_link')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- کلمات کلیدی -->
                            <div>
                                <label for="keywords" class="block text-sm font-medium text-gray-700">کلمات کلیدی (با کاما جدا کنید)</label>
                                <input type="text" name="keywords" id="keywords" value="{{ old('keywords', $book->keywords) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('keywords')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- زبان کتاب -->
                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-700">زبان کتاب</label>
                                <input type="text" name="language" id="language" value="{{ old('language', $book->language) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('language')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- سال انتشار -->
                            <div>
                                <label for="publish_year" class="block text-sm font-medium text-gray-700">سال انتشار</label>
                                <input type="text" name="publish_year" id="publish_year" value="{{ old('publish_year', $book->publish_year) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('publish_year')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- ناشر -->
                            <div>
                                <label for="publisher" class="block text-sm font-medium text-gray-700">ناشر</label>
                                <input type="text" name="publisher" id="publisher" value="{{ old('publisher', $book->publisher) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('publisher')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- فرمت کتاب -->
                            <div>
                                <label for="format" class="block text-sm font-medium text-gray-700">فرمت کتاب</label>
                                <input type="text" name="format" id="format" value="{{ old('format', $book->format) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('format')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- کد کتاب (ISBN) -->
                            <div>
                                <label for="isbn_codes" class="block text-sm font-medium text-gray-700">کدهای کتاب (ISBN) - با کاما جدا کنید</label>
                                <input type="text" name="isbn_codes" id="isbn_codes" value="{{ old('isbn_codes', $book->isbn_codes) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('isbn_codes')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- تصویر جلد -->
                            <div>
                                <label for="cover_image" class="block text-sm font-medium text-gray-700">تصویر جلد</label>
                                @if($book->cover_image)
                                    <div class="mt-2 mb-2">
                                        <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title_fa }}" class="h-32 object-cover">
                                        <p class="text-xs text-gray-500 mt-1">تصویر فعلی</p>
                                    </div>
                                @endif
                                <input type="file" name="cover_image" id="cover_image" class="mt-1 block w-full" accept="image/*">
                                <p class="text-xs text-gray-500 mt-1">برای تغییر تصویر، یک فایل جدید انتخاب کنید.</p>
                                @error('cover_image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- توضیحات فارسی -->
                        <div class="mb-6">
                            <label for="description_fa" class="block text-sm font-medium text-gray-700">توضیحات فارسی</label>
                            <textarea name="description_fa" id="description_fa" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description_fa', $book->description_fa) }}</textarea>
                            @error('description_fa')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- توضیحات انگلیسی -->
                        <div class="mb-6">
                            <label for="description_en" class="block text-sm font-medium text-gray-700">توضیحات انگلیسی</label>
                            <textarea name="description_en" id="description_en" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description_en', $book->description_en) }}</textarea>
                            @error('description_en')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- گزینه‌های اضافی -->
                        <div class="mb-6">
                            <div class="flex items-center mb-2">
                                <input type="checkbox" name="hide_cover" id="hide_cover" value="1" {{ old('hide_cover', $book->hide_cover) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="hide_cover" class="mr-2 text-sm text-gray-700">مخفی کردن تصویر جلد (فقط برای مدیران قابل مشاهده)</label>
                            </div>
                            <div class="flex items-center mb-2">
                                <input type="checkbox" name="is_restricted" id="is_restricted" value="1" {{ old('is_restricted', $book->is_restricted) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="is_restricted" class="mr-2 text-sm text-gray-700">محدود کردن نمایش کتاب (فقط برای مدیران قابل مشاهده)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $book->is_published) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="is_published" class="mr-2 text-sm text-gray-700">منتشر شود</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                بروزرسانی کتاب
                            </button>
                            <a href="{{ route('admin.books.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                انصراف
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
