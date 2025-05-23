<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\CompressSitemapXml;
use App\Http\Middleware\GalleryRateLimiter;
use App\Console\Commands\CompressExistingContent;
use App\Console\Commands\CleanSearchCache;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => IsAdmin::class,
            'compress.sitemap' => CompressSitemapXml::class,
            'gallery.rate.limit' => GalleryRateLimiter::class,
        ]);

        $middleware->web([
            // میدلورهای استاندارد وب
        ]);
    })
    ->withCommands([
        // کامندهای سیستم فشرده‌سازی و بهینه‌سازی
        CompressExistingContent::class,
        CleanSearchCache::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        // تولید روزانه نقشه سایت پست‌ها (ساعت ۲ صبح)
        $schedule->command('sitemap:generate --type=posts')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->description('تولید روزانه نقشه سایت پست‌ها');

        // تولید هفتگی کامل نقشه سایت (یکشنبه ساعت ۳ صبح)
        $schedule->command('sitemap:generate')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping()
            ->description('تولید هفتگی کامل نقشه سایت');

        // پاک‌سازی روزانه کش جستجوی منقضی شده (ساعت ۱ صبح)
        $schedule->command('search:clean-cache')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->description('پاک‌سازی کش جستجوی منقضی شده');

        // فشرده‌سازی هفتگی محتوای جدید (شنبه ساعت ۴ صبح)
        $schedule->command('content:compress --batch=500')
            ->weekly()
            ->saturdays()
            ->at('04:00')
            ->withoutOverlapping()
            ->description('فشرده‌سازی هفتگی محتوای جدید');

        // بهینه‌سازی ماهانه جداول دیتابیس (اول هر ماه ساعت ۵ صبح)
        $schedule->command('db:optimize')
            ->monthlyOn(1, '05:00')
            ->withoutOverlapping()
            ->description('بهینه‌سازی ماهانه جداول دیتابیس');

        // پردازش صف‌های فشرده‌سازی (هر ۱۰ دقیقه)
        $schedule->command('queue:work --queue=compression --stop-when-empty')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->description('پردازش صف‌های فشرده‌سازی');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // تنظیمات مدیریت استثناها
        $exceptions->dontReport([
            \App\Exceptions\CompressionException::class,
        ]);

        // گزارش خطاهای فشرده‌سازی
        $exceptions->report(function (\App\Exceptions\CompressionException $e) {
            \Log::channel('compression')->error('Compression error: ' . $e->getMessage(), [
                'post_id' => $e->getPostId(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        });
    })
    ->create();
