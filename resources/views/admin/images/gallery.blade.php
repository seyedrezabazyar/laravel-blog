{{-- gallery.blade.php - صفحه بررسی نشده --}}
@extends('admin.layouts.layout')

@section('title', 'گالری تصاویر - بررسی نشده')

@section('header-title', 'گالری تصاویر - بررسی نشده')

@section('content')
    <!-- گرید تصاویر -->
    <div class="image-grid" id="image-gallery">
        @forelse ($images as $image)
            <div class="image-item w-1/4 h-[25vw]" data-image-id="{{ $image->id }}">
                <div class="image-container" onclick="showFullscreen('{{ $image->image_url ?? asset('storage/' . $image->image_path) }}')">
                    <span class="status-badge pending-badge">انتظار</span>
                    <img src="{{ $image->image_url ?? asset('storage/' . $image->image_path) }}" alt="تصویر"
                         onerror="this.src='{{ asset('images/default-book.png') }}';">
                </div>
                <div class="image-details">
                    <div>شناسه: {{ $image->id }}</div>
                </div>
                <div class="button-container">
                    <button onclick="approveImage({{ $image->id }})" class="approve-btn">تأیید</button>
                    <button onclick="rejectImage({{ $image->id }})" class="reject-btn">رد</button>
                </div>
            </div>
        @empty
            <div class="w-full text-center py-8 bg-yellow-100 text-yellow-700">
                <p class="font-bold text-lg">هیچ تصویری برای بررسی یافت نشد.</p>
            </div>
        @endforelse
    </div>
@endsection

@section('footer')
    @if(count($images) > 0)
        <!-- دکمه تأیید گروهی -->
        <button onclick="bulkApprove()" class="bulk-approve bg-green-500 hover:bg-green-600 disabled:bg-green-300 w-full py-3 text-white font-bold text-lg transition">تأیید گروهی همه تصاویر</button>
    @endif

    <!-- پیجینیشن -->
    <div class="text-center py-4">
        {{ $images->links() }}
    </div>
@endsection
