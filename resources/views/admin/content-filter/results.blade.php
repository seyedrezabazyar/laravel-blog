<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('نتایج جستجوی محتوا') }}
            </h2>
            <div class="flex space-x-2 space-x-reverse">
                <a href="{{ route('admin.content-filter.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    بازگشت به فیلتر محتوا
                </a>
                <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 1.293a1 1 0 00-1.414 0l-7 7A1 1 0 003 9h1v7a1 1 0 001 1h4a1 1 0 001-1v-4h2v4a1 1 0 001 1h4a1 1 0 001-1V9h1a1 1 0 00.707-1.707l-7-7z" />
                    </svg>
                    مدیریت پست‌ها
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-6 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6 bg-gray-50 p-4 rounded-md">
                        <h3 class="font-semibold text-lg mb-2">کلمات فیلتر شده:</h3>
                        <p class="text-gray-700">{{ $filter_words }}</p>
                    </div>

                    @if(count($posts) > 0)
                        <form action="{{ route('admin.content-filter.bulk-hide') }}" method="POST" id="bulk-hide-form">
                            @csrf

                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <span class="text-gray-700">تعداد پست‌های یافت شده: <strong>{{ $posts->total() }}</strong></span>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                    مخفی کردن موارد انتخاب شده
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            عنوان پست
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            وضعیت
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            عملیات
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($posts as $post)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if(!$post->hide_content)
                                                    <input type="checkbox" name="post_ids[]" value="{{ $post->id }}" class="post-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $post->title }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="flex flex-col space-y-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $post->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        {{ $post->is_published ? 'منتشر شده' : 'پیش‌نویس' }}
                                                    </span>
                                                    @if($post->hide_content)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            مخفی
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-2 space-x-reverse">
                                                    <!-- دکمه نمایش در وبلاگ -->
                                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" title="نمایش در وبلاگ" class="bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md p-2 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>

                                                    <!-- دکمه ویرایش -->
                                                    <a href="{{ route('admin.posts.edit', $post->id) }}" title="ویرایش" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-md p-2 transition-colors">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </a>

                                                    @if($post->hide_content)
                                                        <!-- دکمه نمایش محتوا -->
                                                        <form action="{{ route('admin.content-filter.show-post', $post->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" title="نمایش محتوا" class="bg-green-100 text-green-700 hover:bg-green-200 rounded-md p-2 transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <!-- دکمه مخفی کردن محتوا -->
                                                        <form action="{{ route('admin.content-filter.hide-post', $post->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" title="مخفی کردن محتوا" class="bg-red-100 text-red-700 hover:bg-red-200 rounded-md p-2 transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                                هیچ پستی یافت نشد.
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <!-- پاگینیشن استاندارد لاراول -->
                        <div class="mt-4">
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="bg-yellow-50 p-4 rounded-md text-center">
                            <p class="text-yellow-800 font-medium">هیچ پستی با کلمات کلیدی وارد شده یافت نشد.</p>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('admin.content-filter.index') }}" class="text-indigo-600 hover:text-indigo-900 flex items-center w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            بازگشت به صفحه فیلتر محتوا
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // اضافه کردن قابلیت انتخاب همه/هیچ به چک‌باکس‌ها
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            if (selectAllCheckbox) {
                const postCheckboxes = document.querySelectorAll('.post-checkbox');

                // رویداد برای انتخاب همه
                selectAllCheckbox.addEventListener('change', function() {
                    postCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                });

                // به‌روزرسانی وضعیت انتخاب همه بر اساس چک‌باکس‌های تکی
                postCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const allChecked = Array.from(postCheckboxes).every(cb => cb.checked);
                        const anyChecked = Array.from(postCheckboxes).some(cb => cb.checked);

                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = anyChecked && !allChecked;
                    });
                });

                // اطمینان از اینکه حداقل یک مورد انتخاب شده باشد
                document.getElementById('bulk-hide-form').addEventListener('submit', function(e) {
                    const anyChecked = Array.from(postCheckboxes).some(cb => cb.checked);
                    if (!anyChecked) {
                        e.preventDefault();
                        alert('لطفاً حداقل یک پست را انتخاب کنید.');
                    }
                });
            }
        });
    </script>
</x-app-layout>
