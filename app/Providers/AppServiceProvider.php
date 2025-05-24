<?php

namespace App\Providers;

use App\Models\Publisher;
use App\Services\ElasticsearchService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // در محیط تولید، لاگ کوئری‌های SQL غیرفعال شود
        if ($this->app->environment('production')) {
            DB::disableQueryLog();
        }

        // ثبت ElasticsearchService
        $this->app->singleton(ElasticsearchService::class, function ($app) {
            return new ElasticsearchService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // طول پیش‌فرض رشته‌ها در مایگریشن‌ها
        Schema::defaultStringLength(191);

        // در محیط تولید، از HTTPS استفاده شود
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // بارگذاری بهینه مدل‌های رایج در مسیرها
        Route::bind('publisher', function (string $value) {
            return Publisher::select(['id', 'name', 'slug', 'posts_count'])
                ->where('slug', $value)
                ->firstOrFail();
        });

        // استفاده از Tailwind برای پاگینیشن
        Paginator::useTailwind();

        // ثبت کامپوننت‌های Blade برای بهبود عملکرد
        $this->registerBladeComponents();

        // گوش دادن به کوئری‌های کند (فقط در محیط غیر تولید)
        if (!$this->app->isProduction()) {
            $this->listenToSlowQueries();
        }

        // اشتراک‌گذاری داده‌های ثابت با همه ویوها
        $this->shareGlobalViews();
    }

    /**
     * ثبت کامپوننت‌های Blade
     */
    protected function registerBladeComponents(): void
    {
        Blade::component('components.preload', 'preload');
        Blade::component('components.critical-css', 'critical-css');
        Blade::component('components.blog-card', 'blog-card');
        Blade::component('components.simple-blog-card', 'simple-blog-card');
        Blade::component('components.meta-component', 'meta');

        Blade::directive('cacheBuster', function ($expression) {
            return "<?php echo 'v=' . filemtime(public_path($expression)); ?>";
        });
    }

    /**
     * گوش دادن به کوئری‌های کند
     */
    protected function listenToSlowQueries(): void
    {
        DB::listen(function ($query) {
            if ($query->time > 100) { // کوئری‌های کندتر از 100 میلی‌ثانیه
                Log::info('کوئری کند', [
                    'query' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            }
        });
    }

    /**
     * اشتراک‌گذاری داده‌های ثابت با همه ویوها
     */
    protected function shareGlobalViews(): void
    {
        View::share('appName', config('app.name'));

        // دسته‌بندی‌های ثابت - بدون نیاز به کوئری یا کش
        $globalCategories = [
            (object) ['name' => 'رمان', 'slug' => 'novel', 'posts_count' => 25],
            (object) ['name' => 'علمی', 'slug' => 'science', 'posts_count' => 18],
            (object) ['name' => 'تاریخی', 'slug' => 'history', 'posts_count' => 15],
            (object) ['name' => 'فلسفه', 'slug' => 'philosophy', 'posts_count' => 12],
            (object) ['name' => 'روانشناسی', 'slug' => 'psychology', 'posts_count' => 20],
            (object) ['name' => 'کودک', 'slug' => 'children', 'posts_count' => 10],
            (object) ['name' => 'موفقیت', 'slug' => 'success', 'posts_count' => 22],
            (object) ['name' => 'هنر', 'slug' => 'art', 'posts_count' => 15],
        ];

        View::share('globalCategories', $globalCategories);

        // تنظیم ماکروی کش برای پاسخ‌ها
        Response::macro('cache', function ($seconds = 60) {
            $this->setPublic();
            $this->setMaxAge($seconds);
            $this->headers->addCacheControlDirective('must-revalidate', true);
            return $this;
        });
    }
}
