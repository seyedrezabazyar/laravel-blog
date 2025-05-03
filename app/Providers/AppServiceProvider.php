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

        // بهینه‌سازی بارگذاری مدل‌ها
//        Model::preventLazyLoading(!$this->app->isProduction());

        // ثبت اجزای Blade برای بهبود عملکرد
        $this->registerBladeComponents();

        // ثبت گوش دادن به کوئری‌های کند
        $this->listenToSlowQueries();

        // اشتراک‌گذاری دیدهای عمومی با همه قالب‌ها
        $this->shareGlobalViews();

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
     * اشتراک‌گذاری دیدهای عمومی با همه قالب‌ها
     */
    protected function shareGlobalViews(): void
    {
        // داده‌های مشترک را با تمام قالب‌ها به اشتراک بگذارید
        View::share('appName', config('app.name'));

        // داده‌های کش شده را با تمام قالب‌ها به اشتراک بگذارید
        View::composer('*', function ($view) {
            $globalCategories = Cache::remember('global_categories', 3600, function () {
                return \App\Models\Category::withCount(['posts' => function ($query) {
                    $query->visibleToUser();
                }])
                    ->orderByDesc('posts_count')
                    ->take(8)
                    ->get();
            });

            $view->with('globalCategories', $globalCategories);
        });
    }

    /**
     * تنظیم کنترل کش برای پاسخ‌ها
     */
    protected function setupResponseCaching(): void
    {
        // پاسخ‌ها را برای پردازش بیشتر دستکاری کنید
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
