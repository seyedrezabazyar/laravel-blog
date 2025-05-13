<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\RssController;

// وبلاگ / صفحه اصلی - example.com
Route::get('/', [BlogController::class, 'index'])->name('blog.index');

// داشبورد (با احراز هویت + تأیید ایمیل)
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('authors', AuthorController::class);

        // مسیرهای تگ‌ها - فقط index، edit و update
        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::get('tags/{id}/edit', [TagController::class, 'edit'])->name('tags.edit');
        Route::put('tags/{id}', [TagController::class, 'update'])->name('tags.update');

        // مسیرهای ناشر - به صورت صریح با ترتیب درست
        Route::get('publishers', [PublisherController::class, 'index'])->name('publishers.index');
        Route::get('publishers/create', [PublisherController::class, 'create'])->name('publishers.create');
        Route::post('publishers', [PublisherController::class, 'store'])->name('publishers.store');
        Route::get('publishers/{id}/edit', [PublisherController::class, 'edit'])->name('publishers.edit');
        Route::put('publishers/{id}', [PublisherController::class, 'update'])->name('publishers.update');
        Route::delete('publishers/{id}', [PublisherController::class, 'destroy'])->name('publishers.destroy');

        // مسیرهای تصاویر پست
        Route::delete('post-images/{image}', [PostController::class, 'destroyImage'])->name('post-images.destroy');
        Route::post('post-images/reorder', [PostController::class, 'reorderImages'])->name('post-images.reorder');

// روت‌های گالری تصاویر
        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery');
        Route::get('gallery/visible', [GalleryController::class, 'visible'])->name('gallery.visible');
        Route::get('gallery/hidden', [GalleryController::class, 'hidden'])->name('gallery.hidden');

// API روت‌های گالری برای دریافت و مدیریت تصاویر
        Route::get('api/gallery/images', [GalleryController::class, 'getImages']);
        Route::get('api/gallery/visible', [GalleryController::class, 'getVisibleImages']);
        Route::get('api/gallery/hidden', [GalleryController::class, 'getHiddenImages']);
        Route::post('api/gallery/categorize', [GalleryController::class, 'categorizeImage']);
        Route::post('api/gallery/manage', [GalleryController::class, 'manageImage']);
    });
});

// مسیرهای وبلاگ
// example.com/book/post_slug
Route::get('/book/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// صفحه تگ‌ها - example.com/tags و example.com/tag/tag_slug
Route::get('/tags', [BlogController::class, 'tags'])->name('blog.tags');
Route::get('/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');

// صفحه ناشران - example.com/publishers و example.com/publisher/publisher_slug
Route::get('/publishers', [BlogController::class, 'publishers'])->name('blog.publishers');
Route::get('/publisher/{publisher:slug}', [BlogController::class, 'publisher'])->name('blog.publisher');

// صفحه نویسندگان - example.com/authors و example.com/author/author_slug
Route::get('/authors', [BlogController::class, 'authors'])->name('blog.authors');
Route::get('/author/{author:slug}', [BlogController::class, 'author'])->name('blog.author');

// صفحه دسته‌بندی‌ها - example.com/categories و example.com/category/category_slug
Route::get('/categories', [BlogController::class, 'categories'])->name('blog.categories');
Route::get('/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');

// صفحه جستجو
Route::get('/search', [BlogController::class, 'search'])->name('blog.search');

// مسیرهای نقشه سایت
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
Route::get('sitemap-tags.xml', [SitemapController::class, 'tags']);
Route::get('sitemap-tags-{page}.xml', [SitemapController::class, 'tagsPage'])->where('page', '[0-9]+');

// مسیرهای فید RSS
Route::prefix('feed')->name('feed.')->group(function () {
    Route::get('/', [RssController::class, 'index'])->name('index');
    Route::get('/category/{category:slug}', [RssController::class, 'category'])->name('category');
    Route::get('/author/{author:slug}', [RssController::class, 'author'])->name('author');
    Route::get('/tag/{tag:slug}', [RssController::class, 'tag'])->name('tag');
});

Route::get('/api/footer-partial', function () {
    return response()->view('partials.footer')->header('Cache-Control', 'public, max-age=3600');
});

// مسیرهای احراز هویت
require __DIR__.'/auth.php';
