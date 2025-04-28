@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-md transition duration-300">
    {{-- نمایش تصویر بدون فاصله و با سایز کامل --}}
    <div class="w-full relative">
        @if($post->featuredImage)
            @if(!$post->featuredImage->hide_image || (auth()->check() && auth()->user()->isAdmin()))
                <img
                    src="{{ $post->featuredImage->display_url }}"
                    alt="{{ $post->title }}"
                    class="w-full object-cover" style="height: auto; max-width: 100%; display: block;">
            @else
                <img
                    src="{{ asset('images/default-book.png') }}"
                    alt="{{ $post->title }}"
                    class="w-full object-cover" style="height: auto; max-width: 100%; display: block;">
            @endif
        @else
            <div class="w-full h-48 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center">
                <img src="{{ asset('images/default-book.png') }}" alt="{{ $post->title }}" class="max-h-40 max-w-full">
            </div>
        @endif

        {{-- نمایش متن "تصویر مخفی شده است" روی تصویر فقط برای مدیران --}}
        @if($post->featuredImage && $post->featuredImage->hide_image && auth()->check() && auth()->user()->isAdmin())
            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                <span class="bg-red-600 text-white px-3 py-1 rounded-md text-sm font-medium">
                    تصویر مخفی شده است
                </span>
            </div>
        @endif
    </div>

    <div class="p-4 text-right">
        {{-- عنوان کتاب - سبک جدید مشابه تصویر --}}
        <h3 class="text-xl font-bold mb-2 mt-1">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-gray-800 hover:text-indigo-600">
                {{ $post->title }}
            </a>
        </h3>

        {{-- اطلاعات نویسنده - با فاصله از بالا و پایین --}}
        <div class="text-sm text-gray-600 my-6" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
            @if($post->author || $post->authors->count() > 0)
                <span>
                    نویسنده:
                    @if($post->author)
                        <a href="{{ route('blog.author', $post->author->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ $post->author->name }}
                        </a>
                    @endif

                    @if($post->authors->count() > 0)
                        @if($post->author) <span class="mx-1">،</span> @endif
                        @foreach($post->authors as $index => $author)
                            <a href="{{ route('blog.author', $author->slug) }}" class="text-indigo-600 hover:text-indigo-800">
                                {{ $author->name }}{{ $index < $post->authors->count() - 1 ? '، ' : '' }}
                            </a>
                        @endforeach
                    @endif
                </span>
            @endif
        </div>

        {{-- دکمه مشاهده کتاب - ساده و سبز با بک‌گراند --}}
        <div class="my-3">
            <a href="{{ route('blog.show', $post->slug) }}" class="block w-full text-white text-center py-2 px-4 rounded transition duration-300 font-medium green-button">
                مشاهده کتاب
            </a>

            <style>
                .green-button {
                    background-color: #10B981 !important; /* سبز استاندارد */
                    background-image: linear-gradient(to right, #10B981, #059669) !important;
                    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3) !important;
                    border: none !important;
                }

                .green-button:hover {
                    background-color: #059669 !important;
                    background-image: linear-gradient(to right, #059669, #047857) !important;
                    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4) !important;
                }
            </style>
        </div>

        {{-- فرمت و سال انتشار - شبیه تصویر --}}
        <div class="flex items-center justify-between text-sm text-gray-500 mt-3">
            @if($post->format)
                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded">
                    {{ $post->format }}
                </span>
            @endif
            @if($post->publication_year)
                <span>{{ $post->publication_year }}</span>
            @endif
        </div>
    </div>
</div>
