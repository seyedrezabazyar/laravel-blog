<header class="bg-white shadow-sm py-4">
    <div class="container mx-auto flex justify-between items-center">
        <a href="{{ url('/') }}" class="text-xl font-bold text-indigo-600">کتابستان</a>
        <div class="flex items-center space-x-4 space-x-reverse">
            <a href="{{ route('blog.index') }}" class="text-gray-700 hover:text-indigo-600">وبلاگ</a>
            <form action="{{ route('blog.search') }}" method="GET" class="hidden md:block relative">
                <input type="text" name="q" placeholder="جستجو" class="bg-gray-100 px-3 py-1 rounded-md w-32 lg:w-40">
            </form>
        </div>
    </div>
</header>
