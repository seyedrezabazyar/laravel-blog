<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>گالری تصاویر واقعی | پنل مدیریت</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3a8f88;
            --secondary-color: #4b9e7f;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
        }

        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background-color: #f5f8f7;
            padding: 0;
            margin: 0;
        }

        .gallery-container {
            max-width: 100%;
            margin: 0;
            padding: 0 10px;
        }

        .gallery-header {
            text-align: center;
            margin-bottom: 20px;
            padding-top: 20px;
            color: var(--primary-color);
        }

        .view-toggle {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .view-toggle-btn {
            padding: 8px 20px;
            margin: 0 5px;
            background-color: #e0e0e0;
            color: #555;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: bold;
        }

        .view-toggle-btn:hover {
            background-color: #d0d0d0;
        }

        .view-toggle-btn.active {
            background-color: var(--primary-color);
            color: white;
        }

        /* استایل برای بخش عملیات گروهی */
        .bulk-actions-container {
            background: #e9f7f6;
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 20px;
            margin: 40px auto 20px auto;
            max-width: 800px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }

        .bulk-actions-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
            font-weight: bold;
        }

        .bulk-btn {
            padding: 12px 30px;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0 10px;
            border-radius: 8px;
            transition: all 0.3s;
            min-width: 180px;
        }

        .bulk-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .bulk-btn-approve {
            background-color: var(--success-color);
            border: none;
            color: white;
        }

        .bulk-btn-reject {
            background-color: var(--danger-color);
            border: none;
            color: white;
        }

        .image-card {
            position: relative;
            margin-bottom: 10px;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .image-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .image-container {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
        }

        /* استایل مودال لایت‌باکس */
        .lightbox-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            cursor: zoom-out;
        }

        .lightbox-image {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border: 2px solid white;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 30px;
            cursor: pointer;
            z-index: 2010;
            background-color: rgba(0, 0, 0, 0.5);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .lightbox-info {
            position: absolute;
            bottom: 20px;
            right: 20px;
            left: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            text-align: center;
        }

        .gallery-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
            background-color: #f0f0f0;
            cursor: zoom-in;
        }

        .image-card:hover .gallery-image {
            transform: scale(1.02);
        }

        .image-actions {
            display: flex;
            justify-content: space-between;
            padding: 5px;
            background: white;
        }

        .btn-action {
            flex: 1;
            margin: 0 2px;
            padding: 4px 0;
            border: none;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-green {
            background-color: var(--success-color);
            color: white;
        }

        .btn-red {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
        }

        .fade-out {
            animation: fadeOut 0.5s forwards;
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; height: 0; margin: 0; padding: 0; }
        }

        .loading-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid var(--primary-color);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .empty-gallery {
            text-align: center;
            padding: 50px;
            color: #888;
        }

        .counter {
            text-align: center;
            margin-bottom: 10px;
            font-size: 16px;
            color: var(--primary-color);
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .pagination-btn {
            padding: 8px 16px;
            margin: 0 5px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pagination-btn:hover {
            background-color: var(--secondary-color);
        }

        .pagination-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .page-info {
            margin: 0 15px;
            line-height: 36px;
            color: var(--primary-color);
        }

        .post-link {
            position: absolute;
            top: 5px;
            left: 5px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
            z-index: 10;
            font-size: 12px;
        }

        .post-link:hover {
            background-color: var(--primary-color);
        }

        .image-info {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border-radius: 3px;
            padding: 2px 5px;
            font-size: 10px;
            z-index: 10;
            max-width: 90%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .real-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: var(--success-color);
            color: white;
            border-radius: 3px;
            padding: 2px 5px;
            font-size: 10px;
            z-index: 10;
        }

        .filters-container {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .filter-title {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 10px;
        }

        .search-input {
            display: block;
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .filter-group {
            margin-bottom: 10px;
        }

        .filter-label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .filter-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .filter-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            background-color: var(--secondary-color);
        }

        .filter-reset {
            background-color: #888;
            margin-right: 5px;
        }

        .image-info:hover {
            max-width: none;
            white-space: normal;
            background-color: rgba(0, 0, 0, 0.9);
            word-break: break-all;
        }

        body.lightbox-open {
            overflow: hidden;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .lightbox-modal {
            animation: fadeIn 0.3s ease;
        }

        .col-6 {
            padding: 3px;
        }

        .row {
            margin-right: -3px;
            margin-left: -3px;
        }

        .image-error {
            border: 2px solid var(--danger-color);
        }

        .navbar {
            background-color: var(--primary-color);
            padding: 10px 0;
        }

        .navbar-brand {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            margin: 0 10px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: #e0e0e0;
        }

        .active-nav {
            color: #ffcc00;
            border-bottom: 2px solid #ffcc00;
        }
    </style>
</head>
<body>
<!-- نمایش لودینگ -->
<div class="loading-container" id="loadingContainer">
    <div class="spinner"></div>
</div>

<!-- اضافه کردن المان لایت‌باکس به HTML -->
<div class="lightbox-modal" id="lightboxModal">
    <div class="lightbox-close">&times;</div>
    <img src="" alt="تصویر بزرگنمایی شده" class="lightbox-image" id="lightboxImage">
    <div class="lightbox-info" id="lightboxInfo"></div>
</div>

<!-- منوی ناوبری -->
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="/admin/dashboard">پنل مدیریت</a>
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="/admin/gallery">تصاویر جدید</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/gallery/visible">تصاویر تایید شده</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/admin/gallery/hidden">تصاویر رد شده</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active-nav" href="/admin/gallery/real">تصاویر واقعی</a>
            </li>
        </ul>
    </div>
</nav>

<div class="gallery-container">
    <div class="gallery-header">
        <h1>گالری تصاویر واقعی</h1>
        <p>فقط تصاویری که واقعاً در سرور وجود دارند</p>
    </div>

    <!-- فیلترها -->
    <div class="container">
        <div class="filters-container">
            <div class="filter-title">فیلترها</div>
            <div class="row">
                <div class="col-md-6">
                    <div class="filter-group">
                        <label class="filter-label">جستجو بر اساس شناسه یا نام فایل:</label>
                        <input type="text" class="search-input" id="searchInput" placeholder="شناسه یا نام فایل را وارد کنید...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="filter-group">
                        <label class="filter-label">ترتیب نمایش:</label>
                        <select class="filter-select" id="sortFilter">
                            <option value="newest">جدیدترین</option>
                            <option value="oldest">قدیمی‌ترین</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="filter-group">
                        <button class="filter-btn filter-reset" id="resetFilters">بازنشانی</button>
                        <button class="filter-btn" id="applyFilters">اعمال</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="counter" id="imageCounter">
        تعداد تصاویر واقعی: <span id="totalCount">0</span>
    </div>

    <div class="row" id="imageGallery">
        <!-- تصاویر به صورت پویا اینجا لود می‌شوند -->
    </div>

    <div class="pagination-container">
        <button class="pagination-btn" id="prevPage" disabled>قبلی</button>
        <div class="page-info">صفحه <span id="currentPage">1</span> از <span id="totalPages">1</span></div>
        <button class="pagination-btn" id="nextPage">بعدی</button>
    </div>

    <div class="empty-gallery d-none" id="emptyGallery">
        <h3>هیچ تصویر واقعی موجود نیست</h3>
        <p>تصویری برای نمایش وجود ندارد</p>
    </div>

    <!-- بخش عملیات گروهی در پایین صفحه -->
    <div class="bulk-actions-container">
        <div class="bulk-actions-title">عملیات گروهی</div>
        <div class="d-flex justify-content-center flex-wrap">
            <button class="bulk-btn bulk-btn-approve" onclick="bulkCategorize(false)">تأیید همه</button>
            <button class="bulk-btn bulk-btn-reject" onclick="bulkCategorize(true)">رد همه</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageGallery = document.getElementById('imageGallery');
        const loadingContainer = document.getElementById('loadingContainer');
        const emptyGallery = document.getElementById('emptyGallery');
        const totalCount = document.getElementById('totalCount');
        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');
        const currentPageSpan = document.getElementById('currentPage');
        const totalPagesSpan = document.getElementById('totalPages');
        const searchInput = document.getElementById('searchInput');
        const sortFilter = document.getElementById('sortFilter');
        const applyFilters = document.getElementById('applyFilters');
        const resetFilters = document.getElementById('resetFilters');

        // متغیرهای لایت‌باکس
        const lightboxModal = document.getElementById('lightboxModal');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxInfo = document.getElementById('lightboxInfo');
        const lightboxClose = document.querySelector('.lightbox-close');

        let loadedImages = 0;
        let totalImages = 0;
        let currentPage = 1;
        let totalPages = 1;
        let allImagesData = []; // ذخیره همه تصاویر
        let currentPageData = []; // ذخیره تصاویر صفحه فعلی
        let displayedImageIds = new Set(); // مجموعه شناسه تصاویر نمایش داده شده
        let loadingMoreImages = false; // وضعیت بارگذاری تصاویر بیشتر

        // فیلترهای جستجو
        let filters = {
            search: '',
            sort: 'newest'
        };

        // دریافت تصاویر از سرور
        fetchImages(currentPage);

        // اضافه کردن رویداد کلیک به دکمه‌های صفحه‌بندی
        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                fetchImages(currentPage);
            }
        });

        nextPageBtn.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                fetchImages(currentPage);
            }
        });

        // اضافه کردن رویداد به دکمه‌های فیلتر
        applyFilters.addEventListener('click', () => {
            filters.search = searchInput.value.trim();
            filters.sort = sortFilter.value;
            currentPage = 1;
            fetchImages(currentPage);
        });

        resetFilters.addEventListener('click', () => {
            searchInput.value = '';
            sortFilter.value = 'newest';
            filters = {
                search: '',
                sort: 'newest'
            };
            currentPage = 1;
            fetchImages(currentPage);
        });

        // افزودن قابلیت جستجو با فشردن Enter
        searchInput.addEventListener('keyup', function(event) {
            if (event.key === 'Enter') {
                applyFilters.click();
            }
        });

        function fetchImages(page) {
            // نمایش لودینگ
            loadingContainer.style.display = 'flex';

            // پاک کردن گالری قبلی
            imageGallery.innerHTML = '';
            loadedImages = 0;
            displayedImageIds.clear();

            // ساخت URL برای API با پارامترهای فیلتر
            let apiUrl = `/admin/api/gallery/real?page=${page}`;
            if (filters.search) apiUrl += `&search=${encodeURIComponent(filters.search)}`;
            if (filters.sort) apiUrl += `&sort=${filters.sort}`;

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // بروزرسانی اطلاعات صفحه‌بندی
                    currentPage = data.current_page;
                    totalPages = data.last_page;
                    totalImages = data.data.length;
                    currentPageData = data.data;

                    currentPageSpan.textContent = currentPage;
                    totalPagesSpan.textContent = totalPages;
                    totalCount.textContent = data.total;

                    // فعال/غیرفعال کردن دکمه‌های صفحه‌بندی
                    prevPageBtn.disabled = currentPage === 1;
                    nextPageBtn.disabled = currentPage === totalPages;

                    if (totalImages === 0) {
                        showEmptyGallery();
                        hideLoading();
                        return;
                    }

                    // مخفی کردن پیام خالی بودن گالری
                    emptyGallery.classList.add('d-none');

                    // ذخیره همه تصاویر در آرایه اصلی
                    allImagesData = [...data.data];

                    // ساخت کارت‌های تصویر
                    data.data.forEach(image => {
                        if (!displayedImageIds.has(image.id)) {
                            createImageCard(image);
                            displayedImageIds.add(image.id);
                        }
                    });
                })
                .catch(error => {
                    console.error('خطا در دریافت تصاویر:', error);
                    hideLoading();
                    alert('خطا در دریافت تصاویر: ' + error.message);
                });
        }

        // تابع برای ساخت کارت تصویر
        function createImageCard(image) {
            const colDiv = document.createElement('div');
            colDiv.className = 'col-lg-3 col-md-4 col-sm-6 col-6';
            colDiv.style.padding = '3px';
            colDiv.dataset.imageId = image.id;

            const postTitle = image.post && image.post.title ? image.post.title : 'بدون عنوان';

            const cardHtml = `
                <div class="image-card">
                    <div class="image-container">
                        <img src="${image.raw_image_url}" class="gallery-image" alt="تصویر ${image.id}"
                            data-image-path="${image.image_path || ''}"
                            data-image-id="${image.id}"
                            data-post-id="${image.post_id}"
                            data-post-title="${postTitle}"
                            onload="imageLoaded()" onerror="imageError(this)"
                            onclick="openLightbox(this)">
                        <a href="/admin/posts/${image.post_id}" class="post-link" title="مشاهده پست">
                            <i class="fas fa-link"></i>
                        </a>
                        <div class="image-info">ID: ${image.id} (${image.image_path ? image.image_path.split('/').pop() : 'بدون تصویر'})</div>
                        <div class="real-badge">تصویر واقعی</div>
                    </div>
                    <div class="image-actions">
                        <button class="btn-action btn-green" onclick="categorizeImage(${image.id}, false)">تأیید</button>
                        <button class="btn-action btn-red" onclick="categorizeImage(${image.id}, true)">رد</button>
                    </div>
                </div>
            `;

            colDiv.innerHTML = cardHtml;
            imageGallery.appendChild(colDiv);
        }

        // تابع برای شمارش تصاویر لود شده
        window.imageLoaded = function() {
            loadedImages++;
            if (loadedImages >= totalImages) {
                hideLoading();
            }
        };

        // تابع برای مدیریت خطای لود تصویر
        window.imageError = function(img) {
            console.log('خطا در بارگذاری تصویر:', img.src);
            img.src = '/images/default-book.png'; // تصویر پیش‌فرض
            img.alt = 'تصویر در دسترس نیست';
            // افزودن کلاس خطا برای هایلایت کردن کارت تصاویر با مشکل
            img.closest('.image-card').classList.add('image-error');
            imageLoaded();
        };

        // تابع برای مخفی کردن لودینگ
        function hideLoading() {
            loadingContainer.style.display = 'none';
        }

        // تابع برای نمایش پیام خالی بودن گالری
        function showEmptyGallery() {
            emptyGallery.classList.remove('d-none');
        }

        // تابع برای دسته‌بندی تصویر
        window.categorizeImage = function(imageId, hide) {
            // نمایش افکت محو شدن
            const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
            if (imageElement) {
                const cardElement = imageElement.closest('.col-lg-3');
                if (cardElement) {
                    const imageCard = cardElement.querySelector('.image-card');
                    if (imageCard) {
                        imageCard.classList.add('fade-out');

                        // حذف کارت پس از اتمام انیمیشن
                        setTimeout(() => {
                            // حذف شناسه تصویر از مجموعه تصاویر نمایش داده شده
                            displayedImageIds.delete(parseInt(imageId));

                            // حذف از آرایه تصاویر نمایش داده شده
                            allImagesData = allImagesData.filter(img => img.id !== parseInt(imageId));

                            // حذف کارت
                            cardElement.remove();

                            // بروزرسانی شمارنده
                            const currentCount = parseInt(totalCount.textContent);
                            totalCount.textContent = currentCount - 1;

                            // بررسی خالی بودن گالری در صفحه فعلی
                            if (imageGallery.children.length === 0) {
                                // اگر صفحات دیگری وجود دارد، صفحه بعدی را بارگیری کنید
                                if (currentPage < totalPages) {
                                    fetchImages(currentPage);
                                } else if (currentPage > 1) {
                                    // اگر در آخرین صفحه هستیم، به صفحه قبلی برگردید
                                    fetchImages(currentPage - 1);
                                } else {
                                    // اگر هیچ صفحه‌ای نیست، پیام خالی بودن را نمایش دهید
                                    showEmptyGallery();
                                }
                            }
                        }, 500);
                    }
                }
            }

            // ارسال درخواست به سرور
            fetch('/admin/api/gallery/categorize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    image_id: imageId,
                    hide_image: hide
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('خطا در ذخیره دسته‌بندی:', data.message);
                    }
                })
                .catch(error => {
                    console.error('خطا در ارسال درخواست:', error);
                });
        };

        // تابع برای دسته‌بندی گروهی تصاویر
        window.bulkCategorize = function(hide) {
            if (confirm(`آیا از ${hide ? 'رد' : 'تأیید'} همه تصاویر موجود در این صفحه اطمینان دارید؟`)) {
                // جمع‌آوری تمام شناسه‌های تصاویر نمایش داده شده در صفحه
                const currentImageIds = Array.from(document.querySelectorAll('.gallery-image'))
                    .map(img => parseInt(img.dataset.imageId));

                // نمایش لودینگ
                loadingContainer.style.display = 'flex';

                // ایجاد یک آرایه از وعده‌ها برای ارسال همزمان درخواست‌ها
                const requests = currentImageIds.map(imageId =>
                    fetch('/admin/api/gallery/categorize', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            image_id: imageId,
                            hide_image: hide
                        })
                    }).then(response => response.json())
                );

                // انتظار برای تکمیل همه درخواست‌ها
                Promise.all(requests)
                    .then(results => {
                        console.log(`${results.length} تصویر با موفقیت ${hide ? 'رد' : 'تأیید'} شدند.`);

                        // بروزرسانی شمارنده
                        const currentCount = parseInt(totalCount.textContent);
                        totalCount.textContent = currentCount - currentImageIds.length;

                        // بارگذاری مجدد صفحه فعلی
                        fetchImages(currentPage);
                    })
                    .catch(error => {
                        console.error('خطا در عملیات گروهی:', error);
                        hideLoading();
                    });
            }
        };

        // باز کردن لایت‌باکس
        window.openLightbox = function(imgElement) {
            // ذخیره مکان اسکرول فعلی
            document.body.dataset.scrollPosition = window.pageYOffset;

            // پر کردن اطلاعات لایت‌باکس
            lightboxImage.src = imgElement.src;
            lightboxImage.alt = imgElement.alt;

            // نمایش اطلاعات تصویر
            const imageId = imgElement.dataset.imageId;
            const imagePath = imgElement.dataset.imagePath;
            const postId = imgElement.dataset.postId;
            const postTitle = imgElement.dataset.postTitle;

            lightboxInfo.innerHTML = `
                <strong>شناسه تصویر:</strong> ${imageId} |
                <strong>شناسه پست:</strong> ${postId} |
                <strong>عنوان پست:</strong> ${postTitle} |
                <strong>مسیر تصویر:</strong> ${imagePath || 'نامشخص'}
            `;

            // نمایش لایت‌باکس
            lightboxModal.style.display = 'flex';

            // جلوگیری از اسکرول صفحه پشت لایت‌باکس
            document.body.style.overflow = 'hidden';

            // اضافه کردن کلاس به بدنه برای تغییر استایل
            document.body.classList.add('lightbox-open');

            // جلوگیری از انتشار رویداد کلیک به عناصر زیرین
            event.stopPropagation();
        };

        // بستن لایت‌باکس
        function closeLightbox() {
            lightboxModal.style.display = 'none';

            // بازگرداندن اسکرول صفحه
            document.body.style.overflow = '';

            // برگرداندن به موقعیت اسکرول قبلی
            if (document.body.dataset.scrollPosition) {
                window.scrollTo(0, document.body.dataset.scrollPosition);
            }

            // حذف کلاس از بدنه
            document.body.classList.remove('lightbox-open');
        }

        // افزودن رویدادهای کلیک برای بستن لایت‌باکس
        lightboxModal.addEventListener('click', closeLightbox);
        lightboxClose.addEventListener('click', function(event) {
            event.stopPropagation();
            closeLightbox();
        });

        // جلوگیری از بستن لایت‌باکس هنگام کلیک روی تصویر
        lightboxImage.addEventListener('click', function(event) {
            event.stopPropagation();
        });

        // بستن لایت‌باکس با دکمه ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && lightboxModal.style.display === 'flex') {
                closeLightbox();
            }
        });
    });
</script>

<!-- اضافه کردن Font Awesome برای آیکون‌ها -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
