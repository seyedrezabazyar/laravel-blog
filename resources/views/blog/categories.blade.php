@extends('layouts.blog-app')

@section('content')
    <!-- بخش هدر صفحه با جستجو -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-extrabold text-white mb-6 sm:text-4xl">دسته‌بندی‌های کتاب</h1>
                <p class="text-lg text-indigo-100 mb-8">از طریق دسته‌بندی‌های متنوع، به کتاب‌های مورد علاقه خود دسترسی پیدا کنید</p>

                <!-- باکس جستجوی دسته‌بندی‌ها -->
                <div class="max-w-2xl mx-auto relative">
                    <div class="relative search-container">
                        <input
                            type="text"
                            id="category-search"
                            placeholder="جستجو در دسته‌بندی‌ها..."
                            class="w-full py-4 px-6 rounded-lg shadow-md border-0 focus:ring-2 focus:ring-indigo-400 text-gray-900 text-right search-input"
                        >
                        <div class="search-icon-container">
                            <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- نتایج جستجو (به صورت پیش‌فرض مخفی) -->
                    <div id="search-results" class="search-results-container absolute z-50 bg-white rounded-lg shadow-lg mt-2 w-full hidden opacity-0 transition-opacity duration-300 text-right">
                        <div class="py-2 px-4 border-b border-gray-200">
                            <h3 class="text-sm font-medium text-gray-500">نتایج جستجو</h3>
                        </div>
                        <ul class="search-results-list max-h-64 overflow-y-auto">
                            <!-- نتایج جستجو اینجا به صورت داینامیک اضافه می‌شوند -->
                        </ul>
                        <div id="no-results" class="no-results-message py-6 text-center text-gray-500 hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>نتیجه‌ای یافت نشد!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- بخش دسته‌بندی‌های برتر -->
    <div class="py-12 bg-gradient-to-b from-indigo-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">دسته بندی های سایت</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($categories as $category)
                    <a href="{{ route('blog.category', $category->slug) }}" class="transform transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">
                        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 h-full flex flex-col">
                            <div class="p-6 flex flex-col items-center text-center flex-grow">
                                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mb-4 category-icon" data-category="{{ $category->slug }}">
                                    @php
                                        // ایجاد یک عدد ثابت برای هر دسته بندی بر اساس slug
                                        $iconIndex = crc32($category->slug) % 8;

                                        $icons = [
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />',
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />',
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />',
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />',
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />',
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />',
                                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />',
                                        ];
                                    @endphp
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        {!! $icons[$iconIndex] !!}
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $category->name }}</h3>
                            </div>
                            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-3 text-center">
                                <span class="text-indigo-600 font-medium text-sm">مشاهده کتاب‌ها</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* استایل‌های مخصوص باکس جستجو */
        .search-container {
            position: relative;
            width: 100%;
        }

        .search-input {
            width: 100%;
            padding-right: 1.5rem;
            padding-left: 3rem;
            direction: rtl;
            text-align: right;
        }

        .search-icon-container {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-icon {
            width: 1.5rem;
            height: 1.5rem;
            color: #9ca3af;
        }

        /* استایل‌های کانتینر نتایج جستجو */
        .search-results-container {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
        }

        .search-results-list {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .search-results-list li:last-child {
            border-bottom: none;
        }

        .no-results-message {
            animation: fadeIn 0.3s ease-in-out;
        }

        /* استایل‌های آیکون دسته‌بندی - برای پشتیبانی از تصاویر در آینده */
        .category-icon {
            position: relative;
            overflow: hidden;
        }

        .category-icon img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 9999px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('category-search');
            const searchResults = document.getElementById('search-results');
            const noResults = document.getElementById('no-results');
            const resultsList = searchResults.querySelector('ul');

            // لیست تمام دسته‌بندی‌ها
            const categories = [
                    @foreach($categories as $category)
                { name: "{{ $category->name }}", slug: "{{ $category->slug }}", count: {{ $category->posts_count }} },
                @endforeach
            ];

            // نمایش نتایج جستجو
            function showResults() {
                searchResults.classList.remove('hidden');
                setTimeout(() => {
                    searchResults.classList.remove('opacity-0');
                }, 10);
            }

            // مخفی کردن نتایج جستجو
            function hideResults() {
                searchResults.classList.add('opacity-0');
                setTimeout(() => {
                    searchResults.classList.add('hidden');
                }, 300);
            }

            // جستجو در دسته‌بندی‌ها
            function searchCategories(query) {
                if (!query) {
                    hideResults();
                    return;
                }

                // فیلتر کردن دسته‌بندی‌ها بر اساس عبارت جستجو
                const filteredCategories = categories.filter(category =>
                    category.name.includes(query)
                );

                // نمایش نتایج
                resultsList.innerHTML = '';

                if (filteredCategories.length === 0) {
                    noResults.classList.remove('hidden');
                } else {
                    noResults.classList.add('hidden');

                    filteredCategories.forEach(category => {
                        const listItem = document.createElement('li');
                        listItem.className = 'border-b border-gray-100 last:border-0';
                        listItem.innerHTML = `
                        <a href="/blog/category/${category.slug}" class="block px-4 py-3 hover:bg-gray-50 transition flex justify-between items-center">
                            <span class="font-medium text-gray-700">${category.name}</span>
                            <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2 py-0.5 rounded-full">${category.count} کتاب</span>
                        </a>
                    `;
                        resultsList.appendChild(listItem);
                    });
                }

                showResults();
            }

            // رویداد تغییر مقدار فیلد جستجو
            searchInput.addEventListener('input', function() {
                searchCategories(this.value.trim());
            });

            // رویداد کلیک بیرون از نتایج جستجو
            document.addEventListener('click', function(event) {
                if (!searchResults.contains(event.target) && event.target !== searchInput) {
                    hideResults();
                }
            });

            // رویداد فوکوس روی فیلد جستجو
            searchInput.addEventListener('focus', function() {
                if (this.value.trim()) {
                    searchCategories(this.value.trim());
                }
            });

            // در آینده می‌توان از این تابع برای بارگذاری تصاویر دسته‌بندی‌ها استفاده کرد
            function loadCategoryImages() {
                // این تابع در آینده برای جایگزینی آیکون‌ها با تصاویر واقعی استفاده خواهد شد
                // مثال پیاده‌سازی:
                // document.querySelectorAll('.category-icon').forEach(icon => {
                //     const slug = icon.getAttribute('data-category');
                //     const img = document.createElement('img');
                //     img.src = `/images/categories/${slug}.jpg`;
                //     img.alt = 'تصویر دسته‌بندی';
                //     img.onerror = () => { img.style.display = 'none'; };
                //     img.onload = () => { icon.querySelector('svg').style.display = 'none'; };
                //     icon.appendChild(img);
                // });
            }

            // این تابع را می‌توان در آینده فعال کرد
            // loadCategoryImages();
        });
    </script>
@endpush
