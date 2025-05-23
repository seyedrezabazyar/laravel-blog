@extends('layouts.blog-app')
@section('content')
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $author->name }}</h1>
        </div>
    </div>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <nav class="mb-8 flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4 space-x-reverse">
                <li><a href="{{ route('blog.index') }}" class="text-gray-500 hover:text-gray-700">صفحه اصلی</a></li>
                <li class="flex items-center">
                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="mr-2 text-gray-700 font-medium">نویسنده</span>
                </li>
                <li class="flex items-center">
                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="mr-2 text-gray-700 font-medium">{{ $author->name }}</span>
                </li>
            </ol>
        </nav>
        <div class="bg-white rounded-lg shadow-sm mb-12 md:flex">
            <div class="md:w-1/3 p-6">
                @if($author->image)
                    <img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="{{ asset('storage/' . $author->image) }}" alt="{{ $author->name }}" class="w-full h-auto rounded-lg shadow-md mx-auto max-w-xs lazyload" loading="lazy">
                @else
                    <div class="w-full h-64 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-lg shadow-md flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                @endif
            </div>
            <div class="md:w-2/3 p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">درباره {{ $author->name }}</h2>
                @if($author->biography)
                    <div class="prose max-w-none text-gray-600 mb-6">{!! nl2br(e($author->biography)) !!}</div>
                @else
                    <p class="text-gray-500 italic mb-6">اطلاعات بیوگرافی ثبت نشده است.</p>
                @endif
            </div>
        </div>
        <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-6">کتاب‌های {{ $author->name }}</h3>
            @if($posts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <x-blog-card :post="$post"/>
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @else
                <div class="bg-white p-12 rounded-lg shadow-sm text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">هیچ کتابی یافت نشد</h3>
                    <p class="text-gray-500">به زودی کتاب‌های این نویسنده اضافه خواهد شد.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@push('scripts')
    <script defer>
        document.addEventListener('DOMContentLoaded', () => {
            if ('IntersectionObserver' in window) {
                new IntersectionObserver(entries => entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        entry.target.classList.remove('lazyload');
                        observer.unobserve(img);
                    }
                })).observeAll(document.querySelectorAll('img.lazyload'));
            }
        });
    </script>
@endpush
