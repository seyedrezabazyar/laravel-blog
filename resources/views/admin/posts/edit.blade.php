<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ویرایش کتاب: {{ $post->title }}
            </h2>
            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">بازگشت</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- کارت اصلی فرم -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.posts.update', $post->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- عنوان و دسته‌بندی -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">عنوان کتاب (فارسی)</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">دسته‌بندی</label>
                                <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $post->category_id == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- افزودن فیلدهای انگلیسی -->
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-4">
                            <div>
                                <label for="english_title" class="block text-sm font-medium text-gray-700 mb-1">عنوان کتاب (انگلیسی)</label>
                                <input type="text" name="english_title" id="english_title" value="{{ old('english_title', $post->english_title ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>

                        <!-- محتوای فارسی -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">محتوای کتاب (فارسی)</label>
                            <textarea name="content" id="content" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('content', $post->content) }}</textarea>
                        </div>

                        <!-- محتوای انگلیسی -->
                        <div class="mt-4">
                            <label for="english_content" class="block text-sm font-medium text-gray-700 mb-1">محتوای کتاب (انگلیسی)</label>
                            <textarea name="english_content" id="english_content" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" dir="ltr">{{ old('english_content', $post->english_content ?? '') }}</textarea>
                        </div>

                        <!-- اطلاعات تکمیلی - بدون لیست نویسندگان و ناشران -->
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">اطلاعات تکمیلی کتاب</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="publication_year" class="block text-sm font-medium text-gray-700 mb-1">سال انتشار</label>
                                    <input type="number" name="publication_year" id="publication_year"
                                           value="{{ old('publication_year', $post->publication_year ?? '') }}"
                                           min="1800" max="{{ date('Y') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div>
                                    <label for="language" class="block text-sm font-medium text-gray-700 mb-1">زبان کتاب</label>
                                    <input type="text" name="language" id="language"
                                           value="{{ old('language', $post->language ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div>
                                    <label for="format" class="block text-sm font-medium text-gray-700 mb-1">فرمت کتاب</label>
                                    <input type="text" name="format" id="format"
                                           value="{{ old('format', $post->format ?? '') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>

                            <div>
                                <label for="purchase_link" class="block text-sm font-medium text-gray-700 mb-1">لینک خرید</label>
                                <input type="url" name="purchase_link" id="purchase_link"
                                       value="{{ old('purchase_link', $post->purchase_link ?? '') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>

                        <!-- بخش نویسندگان و ناشر کتاب -->
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">نویسندگان و ناشر</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="authors" class="block text-sm font-medium text-gray-700 mb-1">نویسندگان کتاب</label>
                                    <select name="authors[]" id="authors" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" multiple>
                                        @foreach($authors as $author)
                                            <option value="{{ $author->id }}" {{ in_array($author->id, $post_authors ?? []) ? 'selected' : '' }}>
                                                {{ $author->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">برای انتخاب چند نویسنده، دکمه Ctrl (یا Command در Mac) را نگه دارید.</p>
                                </div>

                                <div>
                                    <label for="publisher_id" class="block text-sm font-medium text-gray-700 mb-1">ناشر</label>
                                    <select name="publisher_id" id="publisher_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">انتخاب ناشر</option>
                                        @foreach($publishers as $publisher)
                                            <option value="{{ $publisher->id }}" {{ isset($post->publisher_id) && $post->publisher_id == $publisher->id ? 'selected' : '' }}>
                                                {{ $publisher->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- بخش تگ‌ها -->
                        <div class="mt-4">
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">برچسب‌های کتاب</label>
                            <input type="text" name="tags" id="tags"
                                   value="{{ old('tags', $tags_list ?? '') }}"                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="برچسب‌ها را با کاما جدا کنید (مثال: رمان، ادبیات معاصر، فانتزی)">
                            <p class="text-xs text-gray-500 mt-1">برچسب‌های مرتبط با کتاب را وارد کنید و آن‌ها را با کاما از هم جدا کنید.</p>
                        </div>

                        <!-- وضعیت انتشار -->
                        <div class="flex items-center space-x-4 space-x-reverse mt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_published" value="1" {{ $post->is_published ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm ml-2">
                                منتشر شود
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" name="hide_content" value="1" {{ $post->hide_content ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm ml-2">
                                مخفی کردن محتوا
                            </label>
                        </div>

                        <!-- دکمه‌های ثبت -->
                        <div class="flex justify-between items-center pt-6 border-t mt-6">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                                ذخیره تغییرات
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
