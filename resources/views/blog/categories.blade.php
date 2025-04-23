<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('دسته‌بندی‌های وبلاگ') }}
            </h2>
            <a href="{{ route('blog.index') }}" class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 transition duration-150 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                بازگشت به صفحه اصلی
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- دسته‌بندی‌های پربازدید -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-6">دسته‌بندی‌های پربازدید</h3>

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        @foreach($popularCategories as $popularCategory)
                            <a href="{{ route('blog.category', $popularCategory->slug) }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 transition">
                                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                                <h4 class="font-medium text-center">{{ $popularCategory->name }}</h4>
                                <span class="mt-2 bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    {{ $popularCategory->posts_count }} پست
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- تمام دسته‌بندی‌ها -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-6">تمام دسته‌بندی‌ها</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($categories as $category)
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition hover-scale">
                                @if($category->sample_post && $category->sample_post->featured_image)
                                    <div class="h-48 overflow-hidden">
                                        <img src="{{ asset('storage/' . $category->sample_post->featured_image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="h-48 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                @endif

                                <div class="p-5">
                                    <div class="flex justify-between items-center mb-3">
                                        <h4 class="text-xl font-semibold">{{ $category->name }}</h4>
                                        <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                            {{ $category->posts_count }} پست
                                        </span>
                                    </div>

                                    <p class="text-gray-600 mb-4">
                                        @if($category->description)
                                            {{ \Illuminate\Support\Str::limit($category->description, 100) }}
                                        @else
                                            مجموعه مطالب مرتبط با {{ $category->name }}
                                        @endif
                                    </p>

                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('blog.category', $category->slug) }}" class="inline-flex items-center font-medium text-indigo-600 hover:text-indigo-800">
                                            مشاهده مطالب
                                            <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </a>

                                        @if($category->sample_post)
                                            <a href="{{ route('blog.show', $category->sample_post->slug) }}" class="text-sm text-gray-500 hover:text-gray-700">
                                                آخرین پست
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- نکته: اگر تعداد دسته‌بندی‌ها زیاد است، می‌توانید از پاگین استفاده کنید -->
            @if($categories->count() > 12)
                <div class="mt-6">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
