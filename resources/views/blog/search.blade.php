@extends('layouts.app')

@section('title', empty($query) ? 'جستجو در کتابخانه' : 'جستجو: ' . $query)

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- فرم جستجو --}}
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <form action="{{ route('search.index') }}" method="GET" class="space-y-4">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text"
                                   name="q"
                                   value="{{ $query }}"
                                   placeholder="عنوان کتاب، نویسنده، ناشر..."
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   id="search-input">
                        </div>
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search ml-2"></i>
                            جستجو
                        </button>
                    </div>

                    {{-- فیلترهای سریع --}}
                    <div class="flex flex-wrap gap-3">
                        @if(!empty($availableFilters['formats']))
                            <select name="format" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">همه فرمت‌ها</option>
                                @foreach($availableFilters['formats'] as $format)
                                    <option value="{{ $format }}" {{ request('format') == $format ? 'selected' : '' }}>
                                        {{ $format }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        @if(!empty($availableFilters['languages']))
                            <select name="language" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">همه زبان‌ها</option>
                                @foreach($availableFilters['languages'] as $language)
                                    <option value="{{ $language }}" {{ request('language') == $language ? 'selected' : '' }}>
                                        {{ $language }}
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        @if(!empty($availableFilters['years']))
                            <select name="year_from" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">از سال</option>
                                @for($year = $availableFilters['years']['max']; $year >= $availableFilters['years']['min']; $year--)
                                    <option value="{{ $year }}" {{ request('year_from') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>

                            <select name="year_to" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">تا سال</option>
                                @for($year = $availableFilters['years']['max']; $year >= $availableFilters['years']['min']; $year--)
                                    <option value="{{ $year }}" {{ request('year_to') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        @endif

                        <a href="{{ route('search.advanced') }}"
                           class="px-4 py-2 text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50 transition-colors text-sm">
                            <i class="fas fa-sliders-h ml-1"></i>
                            جستجوی پیشرفته
                        </a>
                    </div>
                </form>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                {{-- نتایج جستجو --}}
                <div class="flex-1">
                    @if(!empty($query))
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-2">
                                نتایج جستجو برای "{{ $query }}"
                            </h2>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span>
                                <i class="fas fa-list-ol ml-1"></i>
                                {{ number_format($results['total']) }} نتیجه
                            </span>
                                @if($searchTime > 0)
                                    <span>
                                    <i class="fas fa-clock ml-1"></i>
                                    در {{ $searchTime }} میلی‌ثانیه
                                </span>
                                @endif
                            </div>
                        </div>

                        @if($results['total'] > 0)
                            {{-- نتایج --}}
                            <div class="space-y-6">
                                @foreach($results['books'] as $book)
                                    <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow border border-gray-100">
                                        <div class="flex gap-4">
                                            {{-- تصویر کتاب --}}
                                            <div class="flex-shrink-0">
                                                <a href="{{ $book['url'] ?? '#' }}">
                                                    <img src="{{ $book['image_url'] ?? asset('images/default-book.png') }}"
                                                         alt="{{ $book['title'] }}"
                                                         class="w-20 h-28 object-cover rounded-md shadow-sm hover:shadow-md transition-shadow"
                                                         onerror="this.src='{{ asset('images/default-book.png') }}'">
                                                </a>
                                            </div>

                                            {{-- اطلاعات کتاب --}}
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                                    <a href="{{ $book['url'] ?? '#' }}"
                                                       class="hover:text-blue-600 transition-colors">
                                                        @if(isset($book['highlight']['title']))
                                                            {!! implode('...', $book['highlight']['title']) !!}
                                                        @else
                                                            {{ $book['title'] }}
                                                        @endif
                                                    </a>
                                                </h3>

                                                <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-3">
                                                    @if(!empty($book['author']))
                                                        <span class="flex items-center">
                                                        <i class="fas fa-user text-gray-400 ml-1"></i>
                                                        @if(isset($book['highlight']['author']))
                                                                {!! implode('...', $book['highlight']['author']) !!}
                                                            @else
                                                                @if(!empty($book['author_url']))
                                                                    <a href="{{ $book['author_url'] }}" class="hover:text-blue-600">
                                                                    {{ $book['author'] }}
                                                                </a>
                                                                @else
                                                                    {{ $book['author'] }}
                                                                @endif
                                                            @endif
                                                    </span>
                                                    @endif

                                                    @if(!empty($book['category']))
                                                        <span class="flex items-center">
                                                        <i class="fas fa-folder text-gray-400 ml-1"></i>
                                                        @if(!empty($book['category_url']))
                                                                <a href="{{ $book['category_url'] }}" class="hover:text-blue-600">
                                                                {{ $book['category'] }}
                                                            </a>
                                                            @else
                                                                {{ $book['category'] }}
                                                            @endif
                                                    </span>
                                                    @endif

                                                    @if(!empty($book['publisher']))
                                                        <span class="flex items-center">
                                                        <i class="fas fa-building text-gray-400 ml-1"></i>
                                                        @if(!empty($book['publisher_url']))
                                                                <a href="{{ $book['publisher_url'] }}" class="hover:text-blue-600">
                                                                {{ $book['publisher'] }}
                                                            </a>
                                                            @else
                                                                {{ $book['publisher'] }}
                                                            @endif
                                                    </span>
                                                    @endif

                                                    @if(!empty($book['publication_year']))
                                                        <span class="flex items-center">
                                                        <i class="fas fa-calendar text-gray-400 ml-1"></i>
                                                        {{ $book['publication_year'] }}
                                                    </span>
                                                    @endif

                                                    @if(!empty($book['format']))
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                                        {{ $book['format'] }}
                                                    </span>
                                                    @endif
                                                </div>

                                                {{-- خلاصه متن با highlight --}}
                                                @if(isset($book['highlight']['description.persian']) || isset($book['highlight']['description.english']))
                                                    <div class="text-gray-700 text-sm mb-3 line-clamp-3">
                                                        @if(isset($book['highlight']['description.persian']))
                                                            {!! implode('...', $book['highlight']['description.persian']) !!}
                                                        @elseif(isset($book['highlight']['description.english']))
                                                            {!! implode('...', $book['highlight']['description.english']) !!}
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- اطلاعات اضافی --}}
                                                <div class="flex items-center justify-between">
                                                    {{-- امتیاز relevance --}}
                                                    @if(isset($book['score']) && $book['score'] > 0)
                                                        <div class="text-xs text-gray-500">
                                                            <i class="fas fa-star text-yellow-400 ml-1"></i>
                                                            مطابقت: {{ number_format($book['score'], 2) }}
                                                        </div>
                                                    @else
                                                        <div></div>
                                                    @endif

                                                    {{-- لینک مطالعه --}}
                                                    <a href="{{ $book['url'] ?? '#' }}"
                                                       class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors">
                                                        <i class="fas fa-book-open ml-1"></i>
                                                        مطالعه
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- صفحه‌بندی --}}
                            @if($totalPages > 1)
                                <div class="mt-8 flex justify-center">
                                    <nav class="flex items-center gap-2">
                                        @if($hasPrevPage)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}"
                                               class="px-3 py-2 text-gray-500 hover:text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-chevron-right"></i>
                                                قبلی
                                            </a>
                                        @endif

                                        @for($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++)
                                            @if($p == $page)
                                                <span class="px-3 py-2 bg-blue-600 text-white rounded-md">{{ $p }}</span>
                                            @else
                                                <a href="{{ request()->fullUrlWithQuery(['page' => $p]) }}"
                                                   class="px-3 py-2 text-gray-500 hover:text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                                    {{ $p }}
                                                </a>
                                            @endif
                                        @endfor

                                        @if($hasNextPage)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}"
                                               class="px-3 py-2 text-gray-500 hover:text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                                بعدی
                                                <i class="fas fa-chevron-left mr-1"></i>
                                            </a>
                                        @endif
                                    </nav>
                                </div>
                            @endif
                        @else
                            {{-- پیام عدم وجود نتیجه --}}
                            <div class="text-center py-12 bg-white rounded-lg">
                                <i class="fas fa-search text-gray-400 text-6xl mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">نتیجه‌ای یافت نشد</h3>
                                <p class="text-gray-600 mb-4">متأسفانه کتابی با این مشخصات پیدا نکردیم.</p>
                                <div class="space-y-2 text-sm text-gray-500">
                                    <p class="font-medium">پیشنهادات:</p>
                                    <ul class="list-disc list-inside space-y-1 text-right">
                                        <li>کلمات کلیدی مختلفی امتحان کنید</li>
                                        <li>املای کلمات را بررسی کنید</li>
                                        <li>از فیلترهای کمتری استفاده کنید</li>
                                        <li>از جستجوی پیشرفته استفاده کنید</li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- صفحه جستجوی اولیه --}}
                        <div class="text-center py-12 bg-white rounded-lg">
                            <i class="fas fa-search text-blue-500 text-6xl mb-4"></i>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">جستجو در کتابخانه</h2>
                            <p class="text-gray-600 mb-8">عنوان کتاب، نام نویسنده یا ناشر مورد نظر خود را جستجو کنید</p>

                            {{-- دسته‌بندی‌های محبوب --}}
                            @if(!empty($availableFilters['categories']))
                                <div class="max-w-4xl mx-auto">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">دسته‌بندی‌های محبوب</h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        @foreach(array_slice($availableFilters['categories'], 0, 8) as $category)
                                            <a href="{{ route('blog.category', $category['slug']) }}"
                                               class="p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors text-center">
                                                <div class="font-medium text-gray-900">{{ $category['name'] }}</div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="w-full lg:w-80">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-filter ml-2"></i>
                            فیلترها
                        </h3>

                        {{-- دسته‌بندی‌ها --}}
                        @if(!empty($availableFilters['categories']))
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-900 mb-3">دسته‌بندی‌ها</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    @foreach($availableFilters['categories'] as $category)
                                        <a href="{{ request()->fullUrlWithQuery(['category' => $category['name']]) }}"
                                           class="block text-sm text-gray-600 hover:text-blue-600 transition-colors p-2 rounded hover:bg-blue-50 {{ request('category') == $category['name'] ? 'text-blue-600 font-medium bg-blue-50' : '' }}">
                                            {{ $category['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- نویسندگان --}}
                        @if(!empty($availableFilters['authors']))
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-900 mb-3">نویسندگان</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    @foreach($availableFilters['authors'] as $author)
                                        <a href="{{ request()->fullUrlWithQuery(['author' => $author['name']]) }}"
                                           class="block text-sm text-gray-600 hover:text-blue-600 transition-colors p-2 rounded hover:bg-blue-50 {{ request('author') == $author['name'] ? 'text-blue-600 font-medium bg-blue-50' : '' }}">
                                            {{ $author['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ناشران --}}
                        @if(!empty($availableFilters['publishers']))
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-900 mb-3">ناشران</h4>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    @foreach($availableFilters['publishers'] as $publisher)
                                        <a href="{{ request()->fullUrlWithQuery(['publisher' => $publisher['name']]) }}"
                                           class="block text-sm text-gray-600 hover:text-blue-600 transition-colors p-2 rounded hover:bg-blue-50 {{ request('publisher') == $publisher['name'] ? 'text-blue-600 font-medium bg-blue-50' : '' }}">
                                            {{ $publisher['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- پاکسازی فیلترها --}}
                        @if(request()->hasAny(['format', 'language', 'category', 'author', 'publisher', 'year_from', 'year_to']))
                            <a href="{{ route('search.index', ['q' => $query]) }}"
                               class="block w-full text-center px-4 py-2 text-red-600 border border-red-600 rounded-md hover:bg-red-50 transition-colors">
                                <i class="fas fa-times ml-2"></i>
                                پاکسازی فیلترها
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript برای autocomplete --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            let timeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const query = this.value.trim();

                if (query.length < 2) {
                    hideAutocomplete();
                    return;
                }

                timeout = setTimeout(() => {
                    fetch(`{{ route('search.autocomplete') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => showAutocomplete(data))
                        .catch(error => console.error('خطا در autocomplete:', error));
                }, 300);
            });

            function showAutocomplete(suggestions) {
                hideAutocomplete();

                if (suggestions.length === 0) return;

                const dropdown = document.createElement('div');
                dropdown.id = 'autocomplete-dropdown';
                dropdown.className = 'absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto';

                suggestions.forEach(suggestion => {
                    const item = document.createElement('div');
                    item.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0 text-right';
                    item.textContent = suggestion;
                    item.addEventListener('click', () => {
                        searchInput.value = suggestion;
                        hideAutocomplete();
                        searchInput.form.submit();
                    });
                    dropdown.appendChild(item);
                });

                searchInput.parentNode.style.position = 'relative';
                searchInput.parentNode.appendChild(dropdown);
            }

            function hideAutocomplete() {
                const existing = document.getElementById('autocomplete-dropdown');
                if (existing) {
                    existing.remove();
                }
            }

            // بستن autocomplete هنگام کلیک خارج از آن
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target)) {
                    hideAutocomplete();
                }
            });
        });
    </script>

    {{-- استایل‌های اضافی --}}
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        #autocomplete-dropdown {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Highlight styles */
        mark, em {
            background-color: #fef3c7;
            color: #92400e;
            padding: 1px 2px;
            border-radius: 2px;
            font-style: normal;
        }
    </style>
@endsection
