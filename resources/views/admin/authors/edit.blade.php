<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('ویرایش نویسنده') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- نمایش اطلاعات دیباگ -->
                    <div class="mb-6 bg-yellow-50 p-4 rounded border border-yellow-200">
                        <h3 class="font-bold text-lg mb-2">اطلاعات فعلی نویسنده:</h3>
                        <p><strong>شناسه:</strong> {{ $author->id }}</p>
                        <p><strong>نام:</strong> {{ $author->name }}</p>
                        <p><strong>اسلاگ:</strong> {{ $author->slug }}</p>
                        <p><strong>زندگی‌نامه:</strong> {{ $author->biography ?? 'خالی' }}</p>
                        <p><strong>لینک صفحه:</strong> <a href="{{ route('blog.author', $author->slug) }}" target="_blank" class="text-blue-600">{{ route('blog.author', $author->slug) }}</a></p>
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

                    <form action="{{ route('admin.authors.update', $author->id) }}" method="POST" id="authorForm">
                        @csrf
                        @method('PUT')

                        <!-- نام نویسنده -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">نام نویسنده</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $author->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- زندگی‌نامه -->
                        <div class="mb-4">
                            <label for="biography" class="block text-sm font-medium text-gray-700">زندگی‌نامه</label>
                            <textarea name="biography" id="biography" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('biography', $author->biography) }}</textarea>
                            @error('biography')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                بروزرسانی نویسنده
                            </button>
                            <a href="{{ route('admin.authors.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                                انصراف
                            </a>
                        </div>

                        <div class="mt-4 text-sm text-gray-500">
                            <p>برای اطمینان از عملکرد درست فرم، دکمه زیر را بزنید تا داده‌های فرم را ببینید.</p>
                            <button type="button" id="checkFormButton" class="mt-2 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                نمایش داده‌های فرم
                            </button>
                        </div>
                    </form>

                    <div id="formDataDisplay" class="mt-4 hidden p-4 bg-gray-50 rounded-md"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // اسکریپت برای نمایش داده‌های فرم قبل از ارسال
        document.addEventListener('DOMContentLoaded', function() {
            const checkFormButton = document.getElementById('checkFormButton');
            const formDataDisplay = document.getElementById('formDataDisplay');
            const form = document.getElementById('authorForm');

            checkFormButton.addEventListener('click', function() {
                // جمع‌آوری همه داده‌های فرم
                const formData = new FormData(form);
                let formDataHtml = '<h3 class="font-bold mb-2">داده‌های فرم:</h3><ul class="list-disc pl-5">';

                for (let [key, value] of formData.entries()) {
                    formDataHtml += `<li><strong>${key}:</strong> ${value || '(خالی)'}</li>`;
                }

                formDataHtml += '</ul>';
                formDataHtml += `
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                    <p><strong>توجه:</strong> اطمینان حاصل کنید که:</p>
                    <ol class="list-decimal pl-5 mt-2">
                        <li>نام فیلدها در فرم (name, biography) با نام فیلدهای مدل مطابقت دارند.</li>
                        <li>متد form از نوع POST است و اکشن به درستی به آدرس کنترلر اشاره می‌کند.</li>
                        <li>توکن CSRF و متد PUT به درستی در فرم اضافه شده‌اند.</li>
                    </ol>
                </div>`;

                formDataDisplay.innerHTML = formDataHtml;
                formDataDisplay.classList.remove('hidden');
            });
        });
    </script>
</x-app-layout>
