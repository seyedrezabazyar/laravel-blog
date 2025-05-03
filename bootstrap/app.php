
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IsAdmin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => IsAdmin::class,
        ]);

        $middleware->web([
            // اینجا میدل‌ویرهای دیگر را اضافه کنید
            // میدل‌ویر DetectCountry حذف شده است
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // اینجا تنظیمات استثناها را اضافه کنید
    })
    ->create();
