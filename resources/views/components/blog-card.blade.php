@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-md transition duration-300 flex flex-col h-full">
    <!-- تصویر با نسبت ثابت -->
    <div class="w-full relative aspect-[4/3]">
        @php
            $isAdmin = auth()->check() && auth()->user()->isAdmin();

            // استفاده مستقیم از اطلاعات دیتابیس به جای رابطه eloquent
            $dbImage = DB::select('SELECT * FROM post_images WHERE post_id = ? ORDER BY sort_order ASC LIMIT 1', [$post->id]);
            $imageInfo = !empty($dbImage) ? $dbImage[0] : null;

            $showDefaultImage = true;
            $isHidden = false;
            $imageUrl = asset('images/default-book.png');

            if ($imageInfo) {
                if (!empty($imageInfo->image_path)) {
                    if ($isAdmin) {
                        // برای مدیران همیشه تصویر را نمایش بده
                        $imageUrl = $imageInfo->image_path;
                        if (strpos($imageUrl, 'http') !== 0) {
                            $imageUrl = 'https://images.balyan.ir/' . $imageUrl;
                        }
                        $showDefaultImage = false;
                        // بررسی اینکه آیا تصویر مخفی است
                        $isHidden = $imageInfo->hide_image === 'hidden';
                    }
                    else if ($imageInfo->hide_image === 'visible') {
                        // برای کاربران عادی فقط تصاویر visible را نمایش بده
                        $imageUrl = $imageInfo->image_path;
                        if (strpos($imageUrl, 'http') !== 0) {
                            $imageUrl = 'https://images.balyan.ir/' . $imageUrl;
                        }
                        $showDefaultImage = false;
                    }
                }
            }
        @endphp

        @if($showDefaultImage)
            <!-- نمایش تصویر پیش‌فرض -->
            <img
                src="{{ asset('images/default-book.png') }}"
                alt="{{ $post->title }}"
                class="w-full h-full object-cover"
                loading="lazy"
            >
        @else
            <!-- نمایش تصویر واقعی -->
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
        @endif
    </div>

    <!-- محتوای کارت -->
    <div class="p-4 text-right flex-grow flex flex-col">
        <!-- عنوان کتاب -->
        <h3 class="text-xl font-bold mb-2 mt-1 line-clamp-2">
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
                        {{ $post->format }}
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
