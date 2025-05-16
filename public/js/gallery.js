/**
 * نسخه ساده شده gallery.js - فقط برای رفع مشکلات اصلی
 */

// نمایش تصویر در حالت تمام‌صفحه
function showFullscreen(imageSrc) {
    const fullscreenContainer = document.getElementById('fullscreen-container');
    const fullscreenImage = document.getElementById('fullscreen-image');

    fullscreenImage.src = imageSrc;
    fullscreenContainer.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// بستن حالت تمام‌صفحه
function closeFullscreen() {
    document.getElementById('fullscreen-container').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// نمایش اعلان
function showNotification(message, type = 'success', duration = 3000) {
    const notification = document.getElementById('notification');
    if (!notification) return;

    notification.textContent = message;
    notification.style.transform = 'translateY(0)';

    // تنظیم رنگ
    if (type === 'success') {
        notification.style.backgroundColor = '#10B981';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#EF4444';
    } else if (type === 'warning') {
        notification.style.backgroundColor = '#F59E0B';
    } else if (type === 'info') {
        notification.style.backgroundColor = '#3B82F6';
    }

    // نمایش
    notification.style.display = 'block';
    setTimeout(() => { notification.style.opacity = '1'; }, 10);

    // پنهان کردن پس از مدت مشخص
    if (duration > 0) {
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-10px)';
            setTimeout(() => { notification.style.display = 'none'; }, 300);
        }, duration);
    }

    return notification;
}

// تأیید تصویر - اصلاح شده
function approveImage(imageId) {
    const loadingNotification = showNotification('در حال ارسال درخواست...', 'info', 0);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const baseUrl = window.location.origin;

    fetch(`${baseUrl}/admin/gallery/approve/${imageId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`خطای HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (loadingNotification) {
                loadingNotification.style.display = 'none';
            }

            if (data.success) {
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    imageElement.style.opacity = '0';
                    imageElement.style.transform = 'scale(0.8)';

                    setTimeout(() => {
                        imageElement.remove();
                        showNotification('تصویر با موفقیت تأیید شد', 'success');

                        const remainingImages = document.querySelectorAll('#image-gallery [data-image-id]').length;

                        if (remainingImages === 0) {
                            const gallery = document.getElementById('image-gallery');
                            gallery.innerHTML = '<div class="w-full text-center py-8 bg-yellow-100 text-yellow-700"><p class="font-bold text-lg">همه تصاویر بررسی شدند!</p></div>';

                            const bulkButton = document.querySelector('.bulk-approve');
                            if (bulkButton) bulkButton.style.display = 'none';
                        }
                    }, 500);
                }
            }
        })
        .catch(error => {
            if (loadingNotification) {
                loadingNotification.style.display = 'none';
            }

            console.error('خطا:', error);
            showNotification(`خطا: ${error.message || 'خطای نامشخص'}`, 'error');
        });
}

// رد تصویر - اصلاح شده
function rejectImage(imageId) {
    const loadingNotification = showNotification('در حال ارسال درخواست...', 'info', 0);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const baseUrl = window.location.origin;

    fetch(`${baseUrl}/admin/gallery/reject/${imageId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`خطای HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (loadingNotification) {
                loadingNotification.style.display = 'none';
            }

            if (data.success) {
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    imageElement.style.opacity = '0';
                    imageElement.style.transform = 'scale(0.8)';

                    setTimeout(() => {
                        imageElement.remove();
                        showNotification('تصویر با موفقیت رد شد', 'success');

                        const remainingImages = document.querySelectorAll('#image-gallery [data-image-id]').length;

                        if (remainingImages === 0) {
                            const gallery = document.getElementById('image-gallery');
                            gallery.innerHTML = '<div class="w-full text-center py-8 bg-yellow-100 text-yellow-700"><p class="font-bold text-lg">همه تصاویر بررسی شدند!</p></div>';

                            const bulkButton = document.querySelector('.bulk-approve');
                            if (bulkButton) bulkButton.style.display = 'none';
                        }
                    }, 500);
                }
            }
        })
        .catch(error => {
            if (loadingNotification) {
                loadingNotification.style.display = 'none';
            }

            console.error('خطا:', error);
            showNotification(`خطا: ${error.message || 'خطای نامشخص'}`, 'error');
        });
}

// بازگرداندن تصویر - اصلاح شده
function resetImage(imageId) {
    const loadingNotification = showNotification('در حال ارسال درخواست...', 'info', 0);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const baseUrl = window.location.origin;

    fetch(`${baseUrl}/admin/gallery/reset/${imageId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`خطای HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (loadingNotification) {
                loadingNotification.style.display = 'none';
            }

            if (data.success) {
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    imageElement.style.opacity = '0';
                    imageElement.style.transform = 'scale(0.8)';

                    setTimeout(() => {
                        imageElement.remove();
                        showNotification('تصویر با موفقیت بازگردانده شد', 'success');

                        const remainingImages = document.querySelectorAll('#image-gallery [data-image-id]').length;

                        if (remainingImages === 0) {
                            let message = 'هیچ تصویری وجود ندارد.';

                            // تعیین پیام مناسب بر اساس صفحه
                            if (window.location.href.includes('visible')) {
                                message = 'هیچ تصویر تأیید شده‌ای وجود ندارد.';
                            } else if (window.location.href.includes('hidden')) {
                                message = 'هیچ تصویر رد شده‌ای وجود ندارد.';
                            } else if (window.location.href.includes('missing')) {
                                message = 'هیچ تصویر گمشده‌ای وجود ندارد.';
                            }

                            const gallery = document.getElementById('image-gallery');
                            gallery.innerHTML = `<div class="w-full text-center py-8 bg-yellow-100 text-yellow-700"><p class="font-bold text-lg">${message}</p></div>`;
                        }
                    }, 500);
                }
            }
        })
        .catch(error => {
            if (loadingNotification) {
                loadingNotification.style.display = 'none';
            }

            console.error('خطا:', error);
            showNotification(`خطا: ${error.message || 'خطای نامشخص'}`, 'error');
        });
}

// تأیید گروهی - اصلاح شده
function bulkApprove() {
    // جمع‌آوری شناسه‌های تصاویر
    const imageIds = Array.from(document.querySelectorAll('#image-gallery [data-image-id]'))
        .map(element => element.getAttribute('data-image-id'));

    if (imageIds.length === 0) {
        showNotification('هیچ تصویری برای تأیید وجود ندارد', 'warning');
        return;
    }

    // تأیید از کاربر
    if (!confirm(`آیا از تأیید ${imageIds.length} تصویر اطمینان دارید؟`)) {
        return;
    }

    // نمایش وضعیت
    const loadingNotification = showNotification('در حال تأیید تصاویر...', 'info', 0);

    // غیرفعال کردن دکمه
    const bulkButton = document.querySelector('.bulk-approve');
    if (bulkButton) bulkButton.disabled = true;

    // ایجاد داده‌های فرم
    const formData = new FormData();
    imageIds.forEach(id => formData.append('image_ids[]', id));

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const baseUrl = window.location.origin;

    // ارسال درخواست
    fetch(`${baseUrl}/admin/gallery/bulk-approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`خطای HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (loadingNotification) {
                loadingNotification.style.display = 'none';
            }

            if (data.success) {
                // انیمیشن محو شدن تصاویر
                const items = document.querySelectorAll('#image-gallery [data-image-id]');
                items.forEach(item => {
                    item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                });

                setTimeout(() => {
                    showNotification(`${imageIds.length} تصویر با موفقیت تأیید شدند`, 'success');

                    const gallery = document.getElementById('image-gallery');
                    gallery.innerHTML = `
                    <div class="w-full text-center py-8 bg-green-100 text-green-700 animate-pulse">
                        <p class="font-bold text-lg">تمام تصاویر با موفقیت تأیید شدند!</p>
                        <p>لطفاً صفحه را رفرش کنید یا به بخش تصاویر تایید شده بروید.</p>
                    </div>
                    <div class="flex justify-center my-4">
                        <button onclick="location.reload()" class="bg-blue-500 text-white px-4 py-2 rounded mx-2 hover:bg-blue-600 transition">بارگذاری مجدد صفحه</button>
                        <a href="${baseUrl}/admin/gallery/visible" class="bg-green-500 text-white px-4 py-2 rounded mx-2 hover:bg-green-600 transition">مشاهده تصاویر تأیید شده</a>
                    </div>
                `;

                    if (bulkButton) bulkButton.style.display = 'none';
                }, 500);
            }
        })
        .catch(error => {
            if (loadingNotification) {
                loadingNotification.style.display = 'none';
            }

            if (bulkButton) bulkButton.disabled = false;

            console.error('خطا:', error);
            showNotification(`خطا: ${error.message || 'خطای نامشخص'}`, 'error');
        });
}

// کلیدهای میانبر
document.addEventListener('keydown', function(e) {
    // ESC برای بستن حالت تمام‌صفحه
    if (e.key === 'Escape' && document.getElementById('fullscreen-container').style.display === 'flex') {
        closeFullscreen();
        return;
    }

    // میانبرهای کیبورد برای تصویر اول
    const firstImage = document.querySelector('[data-image-id]');
    if (!firstImage) return;

    const imageId = firstImage.getAttribute('data-image-id');

    if (e.key === 'a' || e.key === 'A') {
        approveImage(imageId);
    } else if (e.key === 'r' || e.key === 'R') {
        rejectImage(imageId);
    }
});

// تست اتصال
console.log('gallery.js loaded successfully');
