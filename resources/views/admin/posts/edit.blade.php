<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                ویرایش سریع: {{ $post->title }}
            </h2>
            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">بازگشت</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="bg-yellow-50 p-4 mb-4 rounded-md">
                        <p class="text-yellow-700">این نسخه ساده‌سازی شده برای ویرایش سریع فقط روی این پست خاص است. برای ویرایش کامل، از <a href="{{ route('admin.posts.edit', $post->id) }}" class="underline">ویرایش کامل</a> استفاده کنید.</p>
                    </div>

                    <form action="{{ route('admin.posts.update', $post->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- فیلدهای اصلی -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">عنوان</label>
                                <input type="text" name="title" id="title" value="{{ $post->title }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">دسته‌بندی</label>
                                <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $post->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- وضعیت انتشار -->
                        <div class="flex items-center space-x-4 space-x-reverse mt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_published" value="1" {{ $post->is_published ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm ml-2">
                                منتشر شود
                            </label>

                            <label class="inline-flex items-center">
                                <input type="checkbox" name="hide_content" value="1" {{ $post->hide_content ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm ml-2">
                                محتوا مخفی باشد
                            </label>
                        </div>

                        <div class="flex justify-between pt-4 border-t mt-6">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">ذخیره تغییرات</button>
                            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">انصراف</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
