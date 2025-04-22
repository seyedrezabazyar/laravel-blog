<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('دسته‌بندی:') }} {{ $category->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4">
                        <a href="{{ route('blog.index') }}" class="text-blue-500 hover:underline">بازگشت به وبلاگ</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($posts as $post)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                @if($post->featured_image)
                                    <div class="h-48 overflow-hidden">
                                        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h2 class="text-xl font-semibold mb-2">{{ $post->title }}</h2>
                                    <div class="text-sm text-gray-500 mb-3">
                                        <span>{{ $post->created_at->format('Y/m/d') }}</span> |
                                        <span>{{ $post->user->name }}</span>
                                    </div>
                                    <p class="text-gray-700 mb-3">{{ Str::limit(strip_tags($post->content), 150) }}</p>
                                    <a href="{{ route('blog.show', $post->slug) }}" class="text-blue-500 hover:underline">ادامه مطلب</a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-10">
                                <p class="text-gray-500">هیچ پستی در این دسته‌بندی یافت نشد.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $posts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
