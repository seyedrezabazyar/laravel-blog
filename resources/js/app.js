import './bootstrap';
import '../css/app.css';
import '../css/blog.css'; // اضافه کردن CSS بلاگ

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// اضافه کردن اسکریپت‌های اختصاصی بلاگ
document.addEventListener('DOMContentLoaded', function() {
    // اسکریپت برای منوی موبایل
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });

        // بستن منوی موبایل با کلیک روی آیتم‌های منو
        const mobileMenuItems = document.querySelectorAll('#mobile-menu a');
        mobileMenuItems.forEach(item => {
            item.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
            });
        });
    }

    // اضافه کردن کلاس RTL به تمام عناصر با کلاس space-x
    const spaceXElements = document.querySelectorAll('[class*="space-x-"]');
    spaceXElements.forEach(element => {
        if (document.dir === 'rtl' || document.documentElement.lang === 'fa') {
            element.classList.add('rtl-space-x-reverse');
        }
    });
});
