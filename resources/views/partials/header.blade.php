<header class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center">
                    <span class="text-3xl font-bold text-indigo-600 hover:text-indigo-800 transition-all duration-300">کتابستان</span>
                </a>
            </div>

            <nav class="hidden md:flex space-x-6 space-x-reverse items-center">
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-300 {{ request()->routeIs('blog.index') ? 'text-indigo-600' : '' }}">
                    وبلاگ
                </a>
                <a href="{{ route('blog.category', 'education') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-300 {{ request()->routeIs('blog.category') && request()->segment(2) == 'education' ? 'text-indigo-600' : '' }}">
                    آموزشی
                </a>
                <a href="{{ route('blog.category', 'technology') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-300 {{ request()->routeIs('blog.category') && request()->segment(2) == 'technology' ? 'text-indigo-600' : '' }}">
                    تکنولوژی
                </a>
                <a href="{{ route('blog.category', 'lifestyle') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-300 {{ request()->routeIs('blog.category') && request()->segment(2) == 'lifestyle' ? 'text-indigo-600' : '' }}">
                    سبک زندگی
                </a>
            </nav>

            <!-- باکس جستجو با اصلاح دقیق -->
            <div class="hidden md:flex items-center">
                <form action="{{ route('blog.search') }}" method="GET" class="search-form">
                    <div class="search-container">
                        <input
                            type="text"
                            name="q"
                            placeholder="جستجو"
                            class="search-input"
                            value="{{ request('q') }}"
                            autocomplete="off"
                        >
                        <button type="submit" class="search-button">
                            <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="md:hidden flex items-center">
                <button id="mobile-menu-toggle" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- منوی موبایل تمام صفحه -->
    <div id="mobile-menu" class="md:hidden hidden fixed inset-0 z-50 bg-white">
        <div class="h-full flex flex-col">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">کتابستان</h2>
                <button id="close-mobile-menu" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4">
                <!-- باکس جستجوی موبایل -->
                <div class="mb-6">
                    <form action="{{ route('blog.search') }}" method="GET" class="search-form-mobile">
                        <div class="search-container">
                            <input
                                type="text"
                                name="q"
                                placeholder="جستجو"
                                class="search-input search-input-mobile"
                                value="{{ request('q') }}"
                                autocomplete="off"
                            >
                            <button type="submit" class="search-button">
                                <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <nav class="space-y-3">
                    <a href="{{ route('blog.index') }}" class="block px-3 py-3 rounded-md text-lg font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.index') ? 'text-indigo-600 bg-gray-50' : '' }}">
                        وبلاگ
                    </a>
                    <a href="{{ route('blog.category', 'education') }}" class="block px-3 py-3 rounded-md text-lg font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'education' ? 'text-indigo-600 bg-gray-50' : '' }}">
                        آموزشی
                    </a>
                    <a href="{{ route('blog.category', 'technology') }}" class="block px-3 py-3 rounded-md text-lg font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'technology' ? 'text-indigo-600 bg-gray-50' : '' }}">
                        تکنولوژی
                    </a>
                    <a href="{{ route('blog.category', 'lifestyle') }}" class="block px-3 py-3 rounded-md text-lg font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'lifestyle' ? 'text-indigo-600 bg-gray-50' : '' }}">
                        سبک زندگی
                    </a>
                </nav>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const closeMobileMenu = document.getElementById('close-mobile-menu');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        });

        closeMobileMenu.addEventListener('click', function() {
            mobileMenu.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (mobileMenu && !mobileMenu.classList.contains('hidden') &&
                !mobileMenu.contains(event.target) &&
                !mobileMenuToggle.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap');

    body {
        font-family: 'Vazirmatn', sans-serif;
    }

    /* استایل‌های سفارشی برای جستجو */
    .search-form {
        width: 18rem;
        position: relative;
    }

    .search-form-mobile {
        width: 100%;
        position: relative;
    }

    .search-container {
        position: relative;
        width: 100%;
    }

    .search-input {
        width: 100%;
        padding: 0.625rem 0.875rem 0.625rem 2.5rem;
        background-color: #f3f4f6;
        color: #4b5563;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        outline: none;
        text-align: right;
        direction: rtl;
    }

    .search-input:focus {
        background-color: white;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }

    .search-input-mobile {
        background-color: #f9fafb;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        font-size: 1.05rem;
    }

    .search-button {
        position: absolute;
        left: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        transition: color 0.2s ease;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.25rem;
    }

    .search-button:hover {
        color: #6366f1;
    }

    .search-icon {
        width: 1.25rem;
        height: 1.25rem;
    }

    /* اصلاح سایه */
    .shadow-lg {
        --tw-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
        --tw-shadow-colored: 0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color);
        box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
    }

    /* اصلاحات RTL و پیشنهادات جستجو */
    html[dir="rtl"] .search-button {
        left: 0.5rem;
        right: auto;
    }

    html[dir="rtl"] .search-input {
        padding-left: 2.5rem;
        padding-right: 0.875rem;
    }

    /* اصلاح پیشنهادات جستجو */
    input[type="text"]:-webkit-autofill,
    input[type="text"]:-webkit-autofill:hover,
    input[type="text"]:-webkit-autofill:focus {
        -webkit-text-fill-color: #4b5563;
        -webkit-box-shadow: 0 0 0px 1000px white inset;
        transition: background-color 5000s ease-in-out 0s;
    }

    /* اصلاح پیشنهادات جستجو در مرورگرها */
    input:-webkit-autofill::first-line,
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        font-family: 'Vazirmatn', sans-serif !important;
        font-size: inherit !important;
        text-align: right !important;
    }

    /* اصلاح لیست پیشنهادات */
    input:-webkit-autofill,
    input:-webkit-autofill:focus {
        transition: background-color 600000s 0s, color 600000s 0s;
    }

    /* حذف استایل‌های پیش‌فرض datalist */
    input::-webkit-calendar-picker-indicator {
        display: none !important;
    }
</style>
