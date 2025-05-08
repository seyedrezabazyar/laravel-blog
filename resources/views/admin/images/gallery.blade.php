<!-- resources/views/admin/images/gallery.blade.php -->
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>گالری تصاویر | پنل مدیریت</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3a8f88; /* رنگ بین آبی و سبز، شبیه دریا */
            --secondary-color: #4b9e7f; /* رنگ مکمل، شبیه جنگل */
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
        }
        
        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background-color: #f5f8f7;
            padding: 20px;
        }
        
        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .gallery-header {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        
        .image-card {
            position: relative;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .image-container {
            position: relative;
            padding-top: 75%; /* 4:3 Aspect Ratio */
            overflow: hidden;
        }
        
        .gallery-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .image-card:hover .gallery-image {
            transform: scale(1.05);
        }
        
        .image-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 10px;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .image-actions {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: white;
        }
        
        .btn-action {
            flex: 1;
            margin: 0 5px;
            padding: 8px 0;
            border: none;
            border-radius: 5px;
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
            transform: scale(1.05);
        }
        
        .btn-action:active {
            transform: scale(0.95);
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
            margin-bottom: 20px;
            font-size: 18px;
            color: var(--primary-color);
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            margin-bottom: 30px;
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
            top: 10px;
            left: 10px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .post-link:hover {
            background-color: var(--primary-color);
            transform: scale(1.1);
        }
        
        .image-info {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border-radius: 5px;
            padding: 3px 8px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- نمایش لودینگ -->
    <div class="loading-container" id="loadingContainer">
        <div class="spinner"></div>
    </div>

    <div class="gallery-container">
        <div class="gallery-header">
            <h1>گالری تصاویر</h1>
            <p>لطفاً تصاویر را دسته‌بندی کنید</p>
        </div>
        
        <div class="counter" id="imageCounter">
            تصاویر باقی‌مانده: <span id="remainingCount">0</span>
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
            <h3>تمام تصاویر دسته‌بندی شده‌اند</h3>
            <p>تصویری برای نمایش وجود ندارد</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageGallery = document.getElementById('imageGallery');
            const loadingContainer = document.getElementById('loadingContainer');
            const emptyGallery = document.getElementById('emptyGallery');
            const remainingCount = document.getElementById('remainingCount');
            const prevPageBtn = document.getElementById('prevPage');
            const nextPageBtn = document.getElementById('nextPage');
            const currentPageSpan = document.getElementById('currentPage');
            const totalPagesSpan = document.getElementById('totalPages');
            
            let loadedImages = 0;
            let totalImages = 0;
            let currentPage = 1;
            let totalPages = 1;
            
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
            
            function fetchImages(page) {
                // نمایش لودینگ
                loadingContainer.style.display = 'flex';
                
                // پاک کردن گالری قبلی
                imageGallery.innerHTML = '';
                loadedImages = 0;
                
                fetch(`/admin/api/gallery/images?page=${page}`)
                    .then(response => response.json())
                    .then(data => {
                        // بروزرسانی اطلاعات صفحه‌بندی
                        currentPage = data.current_page;
                        totalPages = data.last_page;
                        totalImages = data.data.length;
                        
                        currentPageSpan.textContent = currentPage;
                        totalPagesSpan.textContent = totalPages;
                        remainingCount.textContent = data.total;
                        
                        // فعال/غیرفعال کردن دکمه‌های صفحه‌بندی
                        prevPageBtn.disabled = currentPage === 1;
                        nextPageBtn.disabled = currentPage === totalPages;
                        
                        if (totalImages === 0) {
                            showEmptyGallery();
                            hideLoading();
                            return;
                        }
                        
                        // ساخت کارت‌های تصویر
                        data.data.forEach(image => {
                            createImageCard(image);
                        });
                    })
                    .catch(error => {
                        console.error('خطا در دریافت تصاویر:', error);
                        hideLoading();
                    });
            }
            
            function createImageCard(image) {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-lg-3 col-md-4 col-sm-6 mb-4';
                colDiv.dataset.imageId = image.id;
                
                const cardHtml = `
                    <div class="image-card">
                        <div class="image-container">
                            <img src="${image.image_url}" class="gallery-image" alt="${image.caption || 'تصویر گالری'}" 
                                onload="imageLoaded()" onerror="imageError(this)">
                            ${image.caption ? `<div class="image-caption">${image.caption}</div>` : ''}
                            <a href="/admin/posts/${image.post_id}" class="post-link" title="مشاهده پست">
                                <i class="fas fa-link"></i>
                            </a>
                            <div class="image-info">ID: ${image.id}</div>
                        </div>
                        <div class="image-actions">
                            <button class="btn-action btn-green" onclick="categorizeImage(${image.id}, true)">تأیید</button>
                            <button class="btn-action btn-red" onclick="categorizeImage(${image.id}, false)">رد</button>
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
                img.src = '/images/default-book.png'; // تصویر پیش‌فرض
                img.alt = 'تصویر در دسترس نیست';
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
window.categorizeImage = function(imageId, hideValue) {
    // نمایش افکت محو شدن
    const imageCard = document.querySelector(`[data-image-id="${imageId}"]`);
    if (imageCard) {
        imageCard.querySelector('.image-card').classList.add('fade-out');
        
        // حذف کارت پس از اتمام انیمیشن
        setTimeout(() => {
            imageCard.remove();
            
            // بروزرسانی شمارنده
            const currentCount = parseInt(remainingCount.textContent);
            remainingCount.textContent = currentCount - 1;
            
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
    
    // ارسال درخواست به سرور
    fetch('/admin/api/gallery/categorize', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            image_id: imageId,
            hide_image: hideValue
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
        });
    </script>
    <!-- اضافه کردن Font Awesome برای آیکون‌ها -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>