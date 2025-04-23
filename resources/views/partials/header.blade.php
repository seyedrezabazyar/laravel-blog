<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center">
                    <span class="text-2xl font-bold text-indigo-600">کتابستان</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex space-x-8 space-x-reverse">
                <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-150 {{ request()->routeIs('blog.index') ? 'text-indigo-600' : '' }}">وبلاگ</a>
                <a href="{{ route('blog.category', 'education') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-150 {{ request()->routeIs('blog.category') && request()->segment(2) == 'education' ? 'text-indigo-600' : '' }}">آموزشی</a>
                <a href="{{ route('blog.category', 'technology') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-150 {{ request()->routeIs('blog.category') && request()->segment(2) == 'technology' ? 'text-indigo-600' : '' }}">تکنولوژی</a>
                <a href="{{ route('blog.category', 'lifestyle') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-md font-medium transition duration-150 {{ request()->routeIs('blog.category') && request()->segment(2) == 'lifestyle' ? 'text-indigo-600' : '' }}">سبک زندگی</a>
            </nav>

            <!-- Search form -->
            <div class="hidden md:block">
                <form action="{{ route('blog.search') }}" method="GET" class="flex">
                    <input type="text" name="q" placeholder="جستجو..." class="px-4 py-1 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ request('q') }}">
                    <button type="submit" class="px-4 py-1 bg-indigo-600 text-white rounded-l-md hover:bg-indigo-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Auth Buttons -->
            <div class="flex items-center space-x-4 space-x-reverse">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800 transition">داشبورد</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md transition">خروج</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 transition">ورود</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md transition">ثبت نام</a>
                    @endif
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none" id="mobile-menu-button">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="hidden md:hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ route('blog.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.index') ? 'text-indigo-600 bg-gray-50' : '' }}">وبلاگ</a>
                <a href="{{ route('blog.category', 'education') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'education' ? 'text-indigo-600 bg-gray-50' : '' }}">آموزشی</a>
                <a href="{{ route('blog.category', 'technology') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'technology' ? 'text-indigo-600 bg-gray-50' : '' }}">تکنولوژی</a>
                <a href="{{ route('blog.category', 'lifestyle') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'lifestyle' ? 'text-indigo-600 bg-gray-50' : '' }}">سبک زندگی</a>
            </div>

            <!-- Mobile search form -->
            <div class="px-2 py-3">
                <form action="{{ route('blog.search') }}" method="GET" class="flex">
                    <input type="text" name="q" placeholder="جستجو..." class="w-full px-4 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" value="{{ request('q') }}">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-l-md hover:bg-indigo-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
