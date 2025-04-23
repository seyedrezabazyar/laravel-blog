<header class="bg-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
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

            <div class="hidden md:flex items-center">
                <form action="{{ route('blog.search') }}" method="GET" class="relative w-72">
                    <input
                        type="text"
                        name="q"
                        placeholder="جستجو"
                        class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:bg-white transition-all duration-300 shadow-lg"
                        value="{{ request('q') }}"
                    >
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

        <div id="mobile-menu" class="md:hidden hidden" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-full">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-indigo-100 rounded-lg shadow-md">
                <div class="mb-4">
                    <form action="{{ route('blog.search') }}" method="GET" class="relative">
                        <input
                            type="text"
                            name="q"
                            placeholder="جستجو"
                            class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:bg-white transition-all duration-300 shadow-lg"
                            value="{{ request('q') }}"
                        >
                    </form>
                </div>

                <a href="{{ route('blog.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.index') ? 'text-indigo-600 bg-gray-50' : '' }}">
                    وبلاگ
                </a>
                <a href="{{ route('blog.category', 'education') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'education' ? 'text-indigo-600 bg-gray-50' : '' }}">
                    آموزشی
                </a>
                <a href="{{ route('blog.category', 'technology') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'technology' ? 'text-indigo-600 bg-gray-50' : '' }}">
                    تکنولوژی
                </a>
                <a href="{{ route('blog.category', 'lifestyle') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50 transition {{ request()->routeIs('blog.category') && request()->segment(2) == 'lifestyle' ? 'text-indigo-600 bg-gray-50' : '' }}">
                    سبک زندگی
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            document.body.style.overflow = mobileMenu.classList.contains('hidden') ? '' : 'hidden';
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600&display=swap');

    body {
        font-family: 'Vazirmatn', sans-serif;
    }
</style>
