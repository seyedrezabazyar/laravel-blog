<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\ImageCheckerController;
use App\Http\Controllers\Admin\ContentFilterController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\RssController;

// وبلاگ / صفحه اصلی
Route::get('/', [BlogController::class, 'index'])->name('blog.index');

// داشبورد (با احراز هویت + تأیید ایمیل)
Route::middleware(['auth', 'verified'])->get('/dashboard', fn () => view('dashboard'))->name('dashboard');

// مسیرهای کاربر احراز هویت شده
Route::middleware('auth')->group(function () {
    // پروفایل
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // پنل مدیریت (فقط برای مدیران)
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // مسیرهای استاندارد دسته‌بندی‌ها، نویسندگان و پست‌ها
        Route::resource('posts', PostController::class)->except(['show', 'destroy']);
        Route::resource('categories', CategoryController::class);
        Route::resource('authors', AuthorController::class)->except(['show', 'destroy', 'create', 'store']);

        // مسیرهای ناشر
        Route::resource('publishers', PublisherController::class)->except(['show']);

        // مسیرهای گالری
        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery');
        Route::get('gallery/visible', [GalleryController::class, 'visible'])->name('gallery.visible');
        Route::get('gallery/hidden', [GalleryController::class, 'hidden'])->name('gallery.hidden');
        Route::get('gallery/missing', [GalleryController::class, 'missing'])->name('gallery.missing');

        // مسیرهای API برای مدیریت تصاویر با محدودیت نرخ
        Route::middleware('gallery.rate.limit')->group(function () {
            Route::post('gallery/approve/{id}', [GalleryController::class, 'approve'])->name('gallery.approve');
            Route::post('gallery/reject/{id}', [GalleryController::class, 'reject'])->name('gallery.reject');
            Route::post('gallery/mark-missing/{id}', [GalleryController::class, 'markMissing'])->name('gallery.mark-missing');
            Route::post('gallery/reset/{id}', [GalleryController::class, 'reset'])->name('gallery.reset');
            Route::post('gallery/bulk-approve', [GalleryController::class, 'bulkApprove'])->name('gallery.bulk-approve');
        });

        // مسیرهای بررسی تصاویر گمشده
        Route::get('images/checker', [ImageCheckerController::class, 'index'])->name('images.checker');
        Route::post('images/check', [ImageCheckerController::class, 'check'])
            ->middleware('gallery.rate.limit')
            ->name('images.check');

        // مسیرهای فیلتر محتوا
        Route::prefix('content-filter')->name('content-filter.')->group(function () {
            Route::get('/', [ContentFilterController::class, 'index'])->name('index');
            Route::post('/', [ContentFilterController::class, 'filter'])->name('filter');
            Route::post('/search', [ContentFilterController::class, 'search'])->name('search');
            Route::post('/hide-post/{id}', [ContentFilterController::class, 'hidePost'])->name('hide-post');
            Route::post('/show-post/{id}', [ContentFilterController::class, 'showPost'])->name('show-post');
            Route::post('/bulk-hide', [ContentFilterController::class, 'bulkHide'])->name('bulk-hide');
        });

        // مسیرهای مدیریت Elasticsearch
        Route::prefix('search')->name('search.')->group(function () {
            Route::get('/stats', [SearchController::class, 'stats'])->name('stats');
            Route::post('/reindex', [SearchController::class, 'reindex'])->name('reindex');
        });
    });
});

// مسیرهای وبلاگ
Route::get('/book/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// مسیرهای دسته‌بندی
Route::get('/categories', [BlogController::class, 'categories'])->name('blog.categories');
Route::get('/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');

// مسیرهای نویسنده
Route::get('/authors', [BlogController::class, 'authors'])->name('blog.authors');
Route::get('/author/{author:slug}', [BlogController::class, 'author'])->name('blog.author');

// مسیرهای ناشر
Route::get('/publishers', [BlogController::class, 'publishers'])->name('blog.publishers');
Route::get('/publisher/{publisher:slug}', [BlogController::class, 'publisher'])->name('blog.publisher');

// مسیرهای جستجو
Route::prefix('search')->name('search.')->group(function () {
    Route::get('/', [SearchController::class, 'index'])->name('index');
    Route::get('/advanced', [SearchController::class, 'advanced'])->name('advanced');
    Route::get('/autocomplete', [SearchController::class, 'autocomplete'])->name('autocomplete');
});

// جستجوی قدیمی (redirect به جستجوی جدید)
Route::get('/blog/search', function() {
    return redirect()->route('search.index', request()->query());
})->name('blog.search');

// مسیرهای نقشه سایت با میدل‌ور کش فشرده‌سازی
Route::middleware('compress.sitemap')->group(function() {
    Route::get('sitemap.xml', [SitemapController::class, 'index']);
    Route::get('sitemap-pages.xml', [SitemapController::class, 'pages']);
    Route::get('sitemap-posts.xml', [SitemapController::class, 'posts']);
    Route::get('sitemap-posts-{page}.xml', [SitemapController::class, 'postsPage'])->where('page', '[0-9]+');
    Route::get('sitemap-post-images.xml', [SitemapController::class, 'postImages']);
    Route::get('sitemap-post-images-{page}.xml', [SitemapController::class, 'postImagesPage'])->where('page', '[0-9]+');
    Route::get('sitemap-categories.xml', [SitemapController::class, 'categories']);
    Route::get('sitemap-categories-{page}.xml', [SitemapController::class, 'categoriesPage'])->where('page', '[0-9]+');
    Route::get('sitemap-authors.xml', [SitemapController::class, 'authors']);
    Route::get('sitemap-authors-{page}.xml', [SitemapController::class, 'authorsPage'])->where('page', '[0-9]+');
    Route::get('sitemap-publishers.xml', [SitemapController::class, 'publishers']);
    Route::get('sitemap-publishers-{page}.xml', [SitemapController::class, 'publishersPage'])->where('page', '[0-9]+');
});

// مسیرهای فید RSS
Route::prefix('feed')->name('feed.')->group(function () {
    Route::get('/', [RssController::class, 'index'])->name('index');
    Route::get('/category/{category:slug}', [RssController::class, 'category'])->name('category');
    Route::get('/author/{author:slug}', [RssController::class, 'author'])->name('author');
});

// کش هدر و فوتر
Route::get('/api/footer-partial', function () {
    return response()->view('partials.footer')->header('Cache-Control', 'public, max-age=3600');
});

// مسیرهای احراز هویت
require __DIR__.'/auth.php';
