@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex justify-center mt-8">
        <div class="flex items-center gap-x-2 rtl:space-x-reverse text-sm font-[inherit]">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 bg-gray-200 text-gray-400 dark:bg-gray-700 dark:text-gray-500 rounded-lg cursor-not-allowed select-none">
                    {{ trans('pagination.previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 bg-white text-indigo-600 border border-gray-300 rounded-lg hover:bg-indigo-50 dark:bg-gray-800 dark:border-gray-600 dark:text-indigo-400 dark:hover:bg-gray-700 transition duration-200 ease-in-out">
                    {{ trans('pagination.previous') }}
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-2 text-gray-500 dark:text-gray-400 select-none">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-2 bg-indigo-600 text-white rounded-lg font-bold shadow-md select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-2 bg-white text-indigo-600 border border-gray-300 rounded-lg hover:bg-indigo-50 dark:bg-gray-800 dark:border-gray-600 dark:text-indigo-400 dark:hover:bg-gray-700 transition duration-200 ease-in-out">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 bg-white text-indigo-600 border border-gray-300 rounded-lg hover:bg-indigo-50 dark:bg-gray-800 dark:border-gray-600 dark:text-indigo-400 dark:hover:bg-gray-700 transition duration-200 ease-in-out">
                    {{ trans('pagination.next') }}
                </a>
            @else
                <span class="px-3 py-2 bg-gray-200 text-gray-400 dark:bg-gray-700 dark:text-gray-500 rounded-lg cursor-not-allowed select-none">
                    {{ trans('pagination.next') }}
                </span>
            @endif
        </div>
    </nav>
@endif
