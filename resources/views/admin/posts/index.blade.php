<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('مدیریت کتاب‌ها') }}
            </h2>
            <a href="{{ route('admin.posts.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                افزودن کتاب جدید
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

                    <!-- لیست ساده پست‌ها -->
                    <div class="bg-white rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    عنوان کتاب
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    عملیات
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($posts as $post)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $post->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2 space-x-reverse">
                                            <a href="{{ route('admin.posts.edit', $post->id) }}" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200 transition duration-150">
                                                ویرایش
                                            </a>
                                            <a href="{{ route('admin.posts.show', $post->id) }}" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition duration-150">
                                                نمایش
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                        هیچ کتابی یافت نشد.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- پاگینیشن ساده دستی -->
                    <div class="flex justify-between items-center mt-6">
                        <div class="text-sm text-gray-600">
                            نمایش {{ $posts ? count($posts) : 0 }} کتاب
                        </div>
                        <div class="flex space-x-2 space-x-reverse">
                            @if($hasPrevious)
                                <a href="{{ route('admin.posts.index', ['page' => $currentPage - 1]) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                    قبلی
                                </a>
                            @else
                                <span class="px-4 py-2 border border-gray-200 rounded-md text-sm bg-gray-100 text-gray-400 cursor-not-allowed">
                                    قبلی
                                </span>
                            @endif

                            @if($hasMore)
                                <a href="{{ route('admin.posts.index', ['page' => $currentPage + 1]) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                    بعدی
                                </a>
                            @else
                                <span class="px-4 py-2 border border-gray-200 rounded-md text-sm bg-gray-100 text-gray-400 cursor-not-allowed">
                                    بعدی
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
