@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-md transition duration-300 flex flex-col h-full">
    {{-- نمایش تصویر با نسبت ثابت - ساده شده --}}
    <div class="w-full relative aspect-[4/3]">
        <img src="{{ asset('images/default-book.png') }}" alt="{{ $post->title }}" class="w-full h-full object-cover"
            loading="lazy">
    </div>

    {{-- محتوای کارت با فاصله مناسب از تصویر --}}
    <div class="p-4 text-right flex-grow flex flex-col">
        {{-- عنوان کتاب --}}
        <h3 class="text-xl font-bold mb-2 mt-1 relative z-10">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-gray-800 hover:text-indigo-600 block">
                {{ $post->title }}
            </a>
        </h3>

        {{-- اطلاعات نویسنده - نمایش ساده شده --}}
        <div class="text-sm text-gray-600 mb-4 flex-grow">
            <div>
                نویسنده: <span class="text-indigo-600">ناشر</span>
            </div>
        </div>

        {{-- دکمه مشاهده کتاب --}}
        <div class="mt-auto">
            <a href="{{ route('blog.show', $post->slug) }}"
                class="block w-full text-white text-center py-2 px-4 rounded transition duration-300 font-medium green-button">
                مشاهده کتاب
            </a>

            {{-- فرمت و سال انتشار --}}
            <div class="flex items-center justify-between text-sm text-gray-500 mt-3">
                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded">
                    {{ $post->format ?? 'PDF' }}
                </span>
                <span>{{ $post->publication_year ?? date('Y') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
    .green-button {
        background-color: #10B981 !important;
        background-image: linear-gradient(to right, #10B981, #059669) !important;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3) !important;
        border: none !important;
    }

    .green-button:hover {
        background-color: #059669 !important;
        background-image: linear-gradient(to right, #059669, #047857) !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
    }

    /* استایل برای نسبت تصویر */
    .aspect-\[4\/3\] {
        aspect-ratio: 4/3;
    }
</style>
