@props(['post'])

<div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-md transition duration-300 flex flex-col h-full">
    <!-- Image with fixed aspect ratio -->
    <div class="w-full relative aspect-[4/3]">
        @if($post->featuredImage && !$post->featuredImage->hide_image)
            <img
                src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
                data-src="{{ $post->featuredImage->display_url }}"
                alt="{{ $post->title }}"
                class="lazyload w-full h-full object-cover"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('images/default-book.png') }}';"
            >
        @else
            <img
                src="{{ asset('images/default-book.png') }}"
                alt="{{ $post->title }}"
                class="w-full h-full object-cover"
                loading="lazy"
            >
        @endif
    </div>

    <!-- Card content -->
    <div class="p-4 text-right flex-grow flex flex-col">
        <!-- Book title -->
        <h3 class="text-xl font-bold mb-2 mt-1 line-clamp-2">
            <a href="{{ route('blog.show', $post->slug) }}" class="text-gray-800 hover:text-blue-600 block">
                {{ $post->title }}
            </a>
        </h3>

        <!-- View button -->
        <div class="mt-auto">
            <a href="{{ route('blog.show', $post->slug) }}"
               class="block w-full text-white text-center py-2 px-4 rounded transition duration-300 font-medium bg-green-500 hover:bg-green-600">
                مشاهده کتاب
            </a>

            <!-- Format and publication year -->
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

<style>
    /* Critical styles for the blog card */
    .aspect-\[4\/3\] {
        aspect-ratio: 4/3;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
