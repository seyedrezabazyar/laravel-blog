@forelse($posts as $post)
    <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            {{ $post->title }}
        </td>
        <!-- ستون‌های دیگر -->
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="flex items-center space-x-2 space-x-reverse">
                <!-- سایر دکمه‌ها -->

                <!-- برای پست 772083، دکمه ویرایش سریع نشان دهید -->
                @if($post->id == 772083)
                    <a href="{{ route('admin.posts.quick-edit', $post->id) }}" title="ویرایش سریع" class="bg-green-100 text-green-700 hover:bg-green-200 rounded-md p-2 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('admin.posts.edit', $post->id) }}" title="ویرایش" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-md p-2 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                @endif

                <!-- سایر دکمه‌ها -->
            </div>
        </td>
    </tr>
@empty
    <!-- کد خالی بودن -->
@endforelse
