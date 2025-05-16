{{-- missing.blade.php - صفحه تصاویر گمشده --}}
@extends('admin.layouts.layout')

@section('title', 'گالری تصاویر - تصاویر گمشده')

@section('header-title', 'گالری تصاویر - تصاویر گمشده')

@section('content')
    <!-- گرید تصاویر -->
    <div class="image-grid" id="image-gallery">
        @forelse ($images as $image)
            <div class="image-item w-1/4 h-[25vw]" data-image-id="{{ $image->id }}">
                <div class="image-container" onclick="showFullscreen('{{ $image->image_url ?? asset('storage/' . $image->image_path) }}')">
                    <span class="status-badge missing-badge">گمشده</span>
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر"
                         onerror="this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="image-details">
                    <div>شناسه: {{ $image->id }}</div>
                    <div class="truncate">{{ basename($image->image_path ?? 'تصویر بدون مسیر') }}</div>
                </div>
                <div class="button-container">
                    <button onclick="resetImage({{ $image->id }})" class="reset-btn">بازگرداندن</button>
                </div>
            </div>
        @empty
            <div class="w-full text-center py-8 bg-yellow-100 text-yellow-700">
                <p class="font-bold text-lg">هیچ تصویر گمشده‌ای یافت نشد.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('footer')
    <!-- پیجینیشن -->
    <div class="text-center py-4">
        {{ $images->links() }}
    </div>
@endsection
