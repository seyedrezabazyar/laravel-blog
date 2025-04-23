<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('مشاهده ناشر') }}
            </h2>
            <div class="flex space-x-2 space-x-reverse">
                <a href="{{ route('admin.publishers.edit', $publisher) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    ویرایش
                </a>
                <a href="{{ route('admin.publishers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150 flex items-center">
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            @if($publisher->logo)
                                <img src="{{ asset('storage/' . $publisher->logo) }}" alt="{{ $publisher->name }}" class="w-full h-auto rounded-lg shadow-lg mb-4">
                            @else
                                <div class="w-full h-64 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-lg shadow-lg flex items-center justify-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $publisher->name }}</h1>

                            <div class="bg-gray-50 p-4 rounded-lg mt-4 mb-6">
                                <h3 class="text-lg font-semibold mb-2">درباره ناشر</h3>
                                <div class="prose max-w-none text-gray-700">
                                    {!! nl2br(e($publisher->description ?? 'اطلاعات توضیحات موجود نیست.')) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- کتاب‌های منتشر شده توسط این ناشر -->
                    <div class="mt-10">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">کتاب‌های این ناشر</h2>

                        @if($publisher->posts->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($publisher->posts as $book)
                                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                                        @if($book->featured_image)
                                            <div class="h-48 overflow-hidden">
                                                <img src="{{ asset('storage/' . $book->featured_image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="h-48 bg-gray-100 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="p-4">
                                            <h3 class="text-xl font-bold mb-2">{{ $book->title }}</h3>
                                            <div class="text-sm text-gray-500 mb-4">
                                                <span>{{ $book->publication_year ?? 'نامشخص' }}</span>
                                                @if($book->author)
                                                    <span class="mx-2">•</span>
                                                    <span>{{ $book->author->name }}</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('admin.posts.edit', $book) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">ویرایش کتاب</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-gray-50 p-6 rounded-lg text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="text-gray-600">هیچ کتابی برای این ناشر ثبت نشده است.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
