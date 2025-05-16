<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\CompressSitemapXml;
use App\Http\Middleware\GalleryRateLimiter;

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
    ->withSchedule(function (Schedule $schedule) {
        // تولید روزانه نقشه سایت پست‌ها (ساعت ۲ صبح)
        $schedule->command('sitemap:generate --type=posts')
            ->dailyAt('02:00')
            ->withoutOverlapping();

        // تولید هفتگی کامل نقشه سایت (یکشنبه ساعت ۳ صبح)
        $schedule->command('sitemap:generate')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // تنظیمات مدیریت استثناها
    })
    // در لاراول 12، می‌توانیم با استفاده از bootstrapWith یا withBootstrappers، رفتار bootstrap را تغییر دهیم
    ->create();
