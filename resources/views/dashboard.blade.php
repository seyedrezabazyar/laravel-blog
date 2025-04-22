<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('داشبورد') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">پست‌ها</h3>
                        <p class="text-2xl">{{ \App\Models\Post::count() }}</p>
                        <div class="mt-4">
                            <a href="{{ route('admin.posts.index') }}" class="text-blue-500 hover:text-blue-700">مشاهده همه پست‌ها</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">دسته‌بندی‌ها</h3>
                        <p class="text-2xl">{{ \App\Models\Category::count() }}</p>
                        <div class="mt-4">
                            <a href="{{ route('admin.categories.index') }}" class="text-blue-500 hover:text-blue-700">مشاهده همه دسته‌بندی‌ها</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">پست‌های منتشر شده</h3>
                        <p class="text-2xl">{{ \App\Models\Post::where('is_published', true)->count() }}</p>
                        <div class="mt-4">
                            <a href="{{ route('blog.index') }}" class="text-blue-500 hover:text-blue-700">مشاهده وبلاگ</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">آخرین پست‌ها</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عنوان</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">دسته‌بندی</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وضعیت</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاریخ</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(\App\Models\Post::with(['category'])->latest()->take(5)->get() as $post)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $post->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $post->category->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($post->is_published)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">منتشر شده</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">پیش‌نویس</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $post->created_at->format('Y/m/d') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-indigo-600 hover:text-indigo-900 ml-2">ویرایش</a>
                                        <a href="{{ route('blog.show', $post->slug) }}" class="text-blue-600 hover:text-blue-900">نمایش</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.posts.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            افزودن پست جدید
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
