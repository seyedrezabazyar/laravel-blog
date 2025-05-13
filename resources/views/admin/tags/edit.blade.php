<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ویرایش تگ') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- نمایش اطلاعات پایه‌ای تگ -->
                    <div class="mb-6 bg-yellow-50 p-4 rounded border border-yellow-200">
                        <h3 class="font-bold text-lg mb-2">اطلاعات تگ:</h3>
                        <p><strong>شناسه:</strong> {{ $tag->id }}</p>
                        <p><strong>لینک صفحه:</strong> <a href="{{ route('blog.tag', $tag->slug) }}" target="_blank" class="text-blue-600">{{ route('blog.tag', $tag->slug) }}</a></p>
                    </div>

                    @if(session('error'))
                        <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="bg-green-100 border-r-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.tags.update', $tag->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- نام تگ -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">نام تگ</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $tag->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- اسلاگ تگ -->
                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700">اسلاگ تگ</label>
                            <div class="flex items-center mt-1">
                                <span class="text-gray-500 bg-gray-100 px-3 py-2 rounded-r-md border border-l-0 border-gray-300">{{ url('/tag/') }}/</span>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $tag->slug) }}" class="flex-1 block w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="slug-example">
                            </div>
                            <p class="text-gray-500 text-xs mt-1">اگر خالی بگذارید، به صورت خودکار از نام تگ ساخته می‌شود.</p>
                            @error('slug')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                بروزرسانی تگ
                            </button>
                            <a href="{{ route('admin.tags.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                انصراف
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // اسکریپت برای ساخت خودکار اسلاگ از نام
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            nameInput.addEventListener('input', function() {
                if (!slugInput.value || slugInput.value === '') {
                    // تبدیل متن به اسلاگ مناسب
                    let slug = nameInput.value
                        .trim()
                        .toLowerCase()
                        .replace(/[^a-z0-9\u0600-\u06FF\s]/g, '') // فقط حروف، اعداد و فضای خالی را نگه می‌دارد
                        .replace(/\s+/g, '-'); // فضاهای خالی را به خط تیره تبدیل می‌کند

                    slugInput.value = slug;
                }
            });
        });
    </script>
</x-app-layout>
