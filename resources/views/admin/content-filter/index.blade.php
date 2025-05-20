<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('فیلتر محتوای غیرمجاز') }}
            </h2>
            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                بازگشت به لیست پست‌ها
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- نمایش پیام‌های سیستم -->
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

            <!-- کارت فیلتر محتوا -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">جستجوی محتوای غیرمجاز</h3>

                    <form action="{{ route('admin.content-filter.filter') }}" method="POST" class="mb-6">
                        @csrf

                        <div class="mb-4">
                            <label for="filter_words" class="block text-sm font-medium text-gray-700 mb-1">کلمات کلیدی غیرمجاز (با کاما جدا شوند)</label>
                            <textarea name="filter_words" id="filter_words" rows="2"
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                      placeholder="مثال: کلمه1، کلمه2، کلمه3"
                                      required>{{ old('filter_words', $lastFilteredWords) }}</textarea>
                            @error('filter_words')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-gray-500 text-xs mt-1">کلمات مورد نظر برای فیلتر را وارد کنید. کلمات با کاما (،) از هم جدا شوند.</p>
                        </div>

                        <div class="flex items-center mb-4">
                            <input type="checkbox" name="hide_content" id="hide_content" value="1"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 ml-2">
                            <label for="hide_content" class="text-sm text-gray-700">
                                مخفی کردن خودکار پست‌های یافت شده
                            </label>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                فیلتر و مخفی‌سازی پست‌ها
                            </button>

                            <a href="{{ route('admin.content-filter.search') }}"
                               onclick="event.preventDefault(); document.getElementById('search-form').submit();"
                               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                فقط جستجو بدون مخفی‌سازی
                            </a>
                        </div>
                    </form>

                    <!-- فرم مخفی برای جستجو بدون مخفی‌سازی -->
                    <form id="search-form" action="{{ route('admin.content-filter.search') }}" method="POST" class="hidden">
                        @csrf
                        <input type="hidden" name="filter_words" id="search_filter_words" value="{{ old('filter_words', $lastFilteredWords) }}">
                    </form>

                    <!-- نمایش نتایج آخرین فیلتر -->
                    @if($lastFilterCount > 0)
                        <div class="mt-8 p-4 bg-gray-50 rounded-md">
                            <h4 class="font-semibold text-gray-700 mb-2">نتیجه آخرین جستجو:</h4>
                            <p>کلمات جستجو شده: <span class="font-semibold">{{ $lastFilteredWords }}</span></p>
                            <p>تعداد پست‌های یافت شده: <span class="font-semibold">{{ $lastFilterCount }}</span></p>

                            @if($lastHiddenCount > 0)
                                <p>تعداد پست‌های مخفی شده: <span class="font-semibold text-red-600">{{ $lastHiddenCount }}</span></p>
                            @endif

                            <div class="mt-2">
                                <a href="{{ route('admin.content-filter.search') }}"
                                   onclick="event.preventDefault(); document.getElementById('search-form').submit();"
                                   class="text-blue-600 hover:text-blue-800 underline text-sm">
                                    مشاهده پست‌های یافت شده
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- کارت فیلترهای قبلی -->
            @if(count($previousFilters) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">فیلترهای قبلی</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($previousFilters as $filter)
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <p class="mb-2 text-gray-800">{{ $filter }}</p>
                                    <div class="flex space-x-2 space-x-reverse">
                                        <button type="button"
                                                onclick="applyFilter('{{ $filter }}')"
                                                class="text-sm text-indigo-600 hover:text-indigo-800">
                                            استفاده از این فیلتر
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- کارت راهنما -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">راهنمای استفاده</h3>

                    <div class="prose prose-lg max-w-none">
                        <ul class="list-disc pr-5 text-gray-700">
                            <li>در بخش کلمات کلیدی، عبارات مورد نظر برای فیلتر را وارد کنید.</li>
                            <li>کلمات را با کاما (،) از هم جدا کنید.</li>
                            <li>اگر می‌خواهید پست‌های یافت شده به صورت خودکار مخفی شوند، گزینه "مخفی کردن خودکار" را انتخاب کنید.</li>
                            <li>گزینه "فقط جستجو" باعث می‌شود پست‌های یافت شده نمایش داده شوند، بدون آنکه مخفی شوند.</li>
                            <li>پس از مخفی شدن پست‌ها، آن‌ها برای کاربران عادی نمایش داده نمی‌شوند اما برای مدیران قابل مشاهده هستند.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // اضافه کردن کد جاوااسکریپت برای به‌روزرسانی فرم‌های جستجو
        document.addEventListener('DOMContentLoaded', function() {
            // همگام‌سازی فیلدهای فرم
            const mainField = document.getElementById('filter_words');
            const searchField = document.getElementById('search_filter_words');

            mainField.addEventListener('input', function() {
                searchField.value = this.value;
            });

            // اعمال فیلتر از لیست فیلترهای قبلی
            window.applyFilter = function(filter) {
                mainField.value = filter;
                searchField.value = filter;
            };
        });
    </script>
</x-app-layout>
