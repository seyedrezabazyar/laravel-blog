<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * ثبت سرویس‌های برنامه.
     */
    public function register(): void
    {
        $this->commands([
            \App\Console\Commands\GenerateSitemaps::class,
        ]);
    }

    /**
     * راه‌اندازی سرویس‌های برنامه.
     */
    public function boot(): void
    {
        //
    }
}
