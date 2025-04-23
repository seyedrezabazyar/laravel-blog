<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ایجاد کتاب جدید') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- عنوان فارسی -->
                            <div>
                                <label for="title_fa" class="block text-sm font-medium text-gray-700">عنوان فارسی</label>
                                <input type="text" name="title_fa" id="title_fa" value="{{ old('title_fa') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                @error('title_fa')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- عنوان انگلیسی -->
                            <div>
                                <label for="title_en" class="block text-sm font-medium text-gray-700">عنوان انگلیسی</label>
                                <input type="text" name="title_en" id="title_en" value="{{ old('title_en') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                        <option value="{{ $author->id }}" {{ in_array($author->id, old('authors', [])) ? 'selected' : '' }}>{{ $author->name }}</option>
                                    @endforeach
                                </select>
                                @error('authors')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- لینک خرید -->
                            <div>
                                <label for="purchase_link" class="block text-sm font-medium text-gray-700">لینک خرید</label>
                                <input type="url" name="purchase_link" id="purchase_link" value="{{ old('purchase_link') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('purchase_link')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- کلمات کلیدی -->
                            <div>
                                <label for="keywords" class="block text-sm font-medium text-gray-700">کلمات کلیدی (با کاما جدا کنید)</label>
                                <input type="text" name="keywords" id="keywords" value="{{ old('keywords') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('keywords')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- زبان کتاب -->
                            <div>
                                <label for="language" class="block text-sm font-medium text-gray-700">زبان کتاب</label>
                                <input type="text" name="language" id="language" value="{{ old('language') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('language')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- سال انتشار -->
                            <div>
                                <label for="publish_year" class="block text-sm font-medium text-gray-700">سال انتشار</label>
                                <input type="text" name="publish_year" id="publish_year" value="{{ old('publish_year') }}" class="mt-1 block w-full
