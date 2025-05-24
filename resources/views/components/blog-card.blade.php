@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-md transition duration-300 flex flex-col h-full">
    <!-- تصویر با نسبت ثابت -->
    <div class="w-full relative aspect-[4/3]">
        @php
            $isAdmin = auth()->check() && auth()->user()->isAdmin();

            // تولید URL تصویر بر اساس فرمول جدید
            $imageUrl = $post->featured_image_url;

            // بررسی وضعیت تصویر اگر رکورد PostImage وجود دارد
            $featuredImage = $post->featuredImage ?? null;
            $isHidden = $featuredImage && $featuredImage->isHidden();
        @endphp

            <!-- نمایش تصویر کتاب -->
        <img
            src="{{ $imageUrl }}"
            alt="{{ $post->title }}"
            class="w-full h-full object-cover"
            loading="lazy"
            onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';"
        >

        @if($isAdmin && $isHidden)
            <!-- نمایش اخطار تصویر مخفی برای مدیران -->
            <div class="absolute inset-0 bg-red-500 bg-opacity-20 flex items-center justify-center">
                <span class="bg-red-600 text-white px-2 py-1 rounded-md text-xs font-bold shadow">تصویر مخفی شده</span>
            </div>
        @endif

        @if($post->hide_content && $isAdmin)
            <!-- نمایش اخطار محتوای مخفی برای مدیران -->
            <div class="absolute top-2 right-2">
                <span class="bg-orange-600 text-white px-2 py-1 rounded-md text-xs font-bold shadow">محتوای مخفی</span>
            </div>
        @endif
    </div>

    <!-- محتوای کارت -->
    <div class="p-4 text-right flex-grow flex flex-col">
        <!-- عنوان کتاب -->
        <h3 class="text-xl font-bold mb-4 mt-1 line-clamp-2">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-gray-800 hover:text-blue-600 block">
                {{ $post->title }}
            </a>
        </h3>

        <!-- دکمه مشاهده -->
        <div class="mt-auto">
            <a href="{{ route('blog.show', $post->slug) }}"
               class="block w-full text-white text-center py-2 px-4 rounded transition duration-300 font-medium bg-green-500 hover:bg-green-600">
                مشاهده کتاب
            </a>

            <!-- فرمت و سال انتشار -->
            <div class="flex items-center justify-between text-sm text-gray-500 mt-3">
                @if($post->format)
                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded">
                        {{ strtoupper($post->format) }}
                    </span>
                @else
                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded">
                        PDF
                    </span>
                @endif
                <span>{{ $post->publication_year ?? date('Y') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
