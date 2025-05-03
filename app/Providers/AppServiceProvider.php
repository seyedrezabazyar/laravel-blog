<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // اگر در محیط تولید هستیم، ردیابی SQL را غیرفعال می‌کنیم
        if ($this->app->environment('production')) {
            DB::disableQueryLog();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // برای اطمینان از اینکه طول رشته‌ها در مهاجرت‌ها مشکلی ایجاد نمی‌کند
        Schema::defaultStringLength(191);

        // برای ترجیح URL های HTTPS در محیط تولید
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // استفاده از Bootstrap در Pagination
        Paginator::useBootstrap();

        // ثبت اجزای Blade برای بهبود عملکرد
        $this->registerBladeComponents();

        // ثبت گوش دادن به کوئری‌های کند
        $this->listenToSlowQueries();

        // اشتراک‌گذاری دیدهای عمومی به صورت ثابت (بدون کوئری به دیتابیس)
        $this->shareGlobalViewsWithoutQueries();

        // تنظیم کنترل کش برای پاسخ‌ها
        $this->setupResponseCaching();
    }

    /**
     * ثبت اجزای Blade برای بهبود عملکرد
     */
    protected function registerBladeComponents(): void
    {
        // اجزای مهم Blade را ثبت کنید
        Blade::component('components.preload', 'preload');
        Blade::component('components.critical-css', 'critical-css');
        Blade::component('components.blog-card', 'blog-card');
        Blade::component('components.simple-blog-card', 'simple-blog-card');
        Blade::component('components.meta-component', 'meta');

        // دستورات مفید Blade را تعریف کنید
        Blade::directive('cacheBuster', function ($expression) {
            return "<?php echo 'v=' . filemtime(public_path($expression)); ?>";
        });
    }

    /**
     * گوش دادن به کوئری‌های کند
     */
    protected function listenToSlowQueries(): void
    {
        // فقط در محیط غیر تولیدی
        if (!$this->app->isProduction()) {
            DB::listen(function ($query) {
                if ($query->time > 100) { // کوئری‌های کندتر از 100 میلی‌ثانیه را ثبت کنید
                    Log::info('کوئری کند', [
                        'query' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time,
                    ]);
                }
            });
        }
    }

    /**
     * اشتراک‌گذاری دیدهای عمومی با همه قالب‌ها - بدون کوئری
     */
    protected function shareGlobalViewsWithoutQueries(): void
    {
        // داده‌های مشترک را با تمام قالب‌ها به اشتراک بگذارید
        View::share('appName', config('app.name'));

        // دسته‌بندی‌های ثابت - بدون نیاز به کش یا کوئری
        $globalCategories = [
            (object) ['name' => 'رمان', 'slug' => 'roman', 'posts_count' => 25],
            (object) ['name' => 'علمی', 'slug' => 'scientific', 'posts_count' => 18],
            (object) ['name' => 'تاریخی', 'slug' => 'historical', 'posts_count' => 15],
            (object) ['name' => 'فلسفه', 'slug' => 'philosophy', 'posts_count' => 12],
            (object) ['name' => 'روانشناسی', 'slug' => 'psychology', 'posts_count' => 20],
            (object) ['name' => 'کودک', 'slug' => 'children', 'posts_count' => 10],
            (object) ['name' => 'موفقیت', 'slug' => 'success', 'posts_count' => 22],
            (object) ['name' => 'هنر', 'slug' => 'art', 'posts_count' => 15],
        ];

        // مستقیماً به اشتراک بگذارید - بدون کوئری کش
        View::share('globalCategories', $globalCategories);
    }

    /**
     * تنظیم کنترل کش برای پاسخ‌ها
     */
    protected function setupResponseCaching(): void
    {
        // پاسخ‌ها را برای کش کردن بهتر پیکربندی می‌کنیم
        Response::macro('cache', function ($seconds = 60) {
            $response = $this;

            if (!$response->headers->has('Cache-Control')) {
                $response->setPublic();
                $response->setMaxAge($seconds);
                $response->headers->addCacheControlDirective('must-revalidate', true);
            }

            return $response;
        });
    }
}
