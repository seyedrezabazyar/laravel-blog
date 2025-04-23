<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('ویرایش پست') }}: {{ $post->title }}
            </h2>
            <div class="flex space-x-2 space-x-reverse">
                <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    بازگشت به لیست پست‌ها
                </a>
                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                    </svg>
                    مشاهده در وبلاگ
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">عنوان</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('title') border-red-300 @enderror"
                                       required>
                                @error('title')
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
                                        <option value="{{ $category->id }}" {{ (old('category_id', $post->category_id) == $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">محتوا</label>
                            <textarea name="content" id="content" rows="15"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('content') border-red-300 @enderror"
                                      required>{{ old('content', $post->content) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">از تگ‌های HTML برای قالب‌بندی متن استفاده کنید.</p>
                            @error('content')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1">تصویر شاخص</label>
                            @if($post->featured_image)
                                <div class="mt-2 mb-4">
                                    <div class="relative group w-full max-w-lg">
                                        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="h-48 w-full object-cover rounded-lg shadow-sm">
                                        <div class="absolute inset-0 bg-gray-900 bg-opacity-50 opacity-0 group-hover:opacity-100 rounded-lg flex items-center justify-center transition-opacity">
                                            <span class="text-white text-sm">تصویر فعلی</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="mt-1 flex items-center">
                                <input type="file" name="featured_image" id="featured_image"
                                       class="w-full border border-gray-300 rounded-md p-2 @error('featured_image') border-red-300 @enderror"
                                       accept="image/*">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">برای تغییر تصویر، یک فایل جدید انتخاب کنید. در غیر این صورت، تصویر فعلی حفظ می‌شود.</p>
                            @error('featured_image')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_published" id="is_published" value="1"
                                   {{ old('is_published', $post->is_published) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ml-2">
                            <label for="is_published" class="text-sm text-gray-700">منتشر شود</label>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    بروزرسانی پست
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
            // اینجا می‌توانید اسکریپت‌های مربوط به ویرایشگر متن را قرار دهید
            document.addEventListener('DOMContentLoaded', function() {
                // به عنوان مثال، اگر از CKEditor استفاده می‌کنید:
                // ClassicEditor.create(document.querySelector('#content'));
            });
        </script>
    @endpush
</x-app-layout>
