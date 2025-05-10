<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\SitemapController;

class GenerateSitemaps extends Command
{
    /**
     * نام و امضای دستور.
     */
    protected $signature = 'sitemap:generate {--type=all} {--force}';

    /**
     * توضیحات دستور.
     */
    protected $description = 'تولید فایل‌های نقشه سایت برای وب‌سایت';

    /**
     * اجرای دستور.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $type = $this->option('type');
        $force = $this->option('force');

        // پاک کردن کش در صورت اجبار
        if ($force) {
            $this->info('در حال پاک کردن کش نقشه سایت...');
            $this->clearSitemapCache();
        }

        $this->info('شروع تولید نقشه سایت...');

        // ایجاد کنترلر نقشه سایت
        $controller = new SitemapController();

        // تولید نقشه سایت‌های مناسب بر اساس نوع
        if ($type === 'all' || $type === 'home') {
            $this->info('تولید نقشه سایت صفحه اصلی...');
            $controller->home();
        }

        if ($type === 'all' || $type === 'static') {
            $this->info('تولید نقشه سایت صفحات استاتیک...');
            $controller->static();
        }

        if ($type === 'all' || $type === 'posts') {
            $this->generateTypeSitemaps($controller, 'posts');
        }

        if ($type === 'all' || $type === 'categories') {
            $this->generateTypeSitemaps($controller, 'categories');
        }

        if ($type === 'all' || $type === 'authors') {
            $this->generateTypeSitemaps($controller, 'authors');
        }

        if ($type === 'all' || $type === 'publishers') {
            $this->generateTypeSitemaps($controller, 'publishers');
        }

        if ($type === 'all' || $type === 'tags') {
            $this->generateTypeSitemaps($controller, 'tags');
        }

        if ($type === 'all' || $type === 'images') {
            $this->generateTypeSitemaps($controller, 'images');
        }

        // همیشه فایل شاخص را در آخر تولید کن
        $this->info('تولید شاخص نقشه سایت...');
        $controller->index();

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime);

        $this->info('تولید نقشه سایت در ' . number_format($executionTime, 2) . ' ثانیه به پایان رسید!');
    }

    /**
     * پاک کردن تمام کش نقشه سایت
     */
    protected function clearSitemapCache()
    {
        // پاک کردن کش‌های اصلی نقشه سایت
        Cache::forget('sitemap_index');
        Cache::forget('sitemap_home');
        Cache::forget('sitemap_static');

        // پاک کردن شمارنده‌ها
        Cache::forget('sitemap_posts_count');
        Cache::forget('sitemap_categories_count');
        Cache::forget('sitemap_authors_count');
        Cache::forget('sitemap_publishers_count');
        Cache::forget('sitemap_tags_count');
        Cache::forget('sitemap_images_count');

        // پاک کردن کش‌های صفحه خاص
        foreach (['posts', 'categories', 'authors', 'publishers', 'tags', 'images'] as $type) {
            // حذف ۱۰۰ صفحه اول (بیشتر از این تعداد غیرمعمول است)
            for ($i = 1; $i <= 100; $i++) {
                Cache::forget("sitemap_{$type}_page_{$i}");
            }
        }
    }

    /**
     * تولید نقشه سایت برای یک نوع محتوا
     */
    protected function generateTypeSitemaps($controller, $type)
    {
        // گرفتن تعداد صفحات نقشه سایت از کنترلر
        $method = 'getSitemapCount';
        $reflection = new \ReflectionObject($controller);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        $count = $method->invoke($controller, $type);

        $this->info("تولید {$count} نقشه سایت برای {$type}...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // تولید هر صفحه نقشه سایت
        for ($page = 1; $page <= $count; $page++) {
            // فراخوانی متد مناسب در کنترلر
            $controller->$type($page);

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nنقشه سایت‌های {$type} با موفقیت تولید شدند!");
    }
}
