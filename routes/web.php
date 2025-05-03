<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\PublisherController;
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
        Route::resources([
            'posts' => PostController::class,
            'categories' => CategoryController::class,
            'authors' => AuthorController::class,
            'publishers' => PublisherController::class,
        ]);

        Route::delete('post-images/{image}', [PostController::class, 'destroyImage'])->name('post-images.destroy');
        Route::post('post-images/reorder', [PostController::class, 'reorderImages'])->name('post-images.reorder');
    });
});

// مسیرهای وبلاگ
// example.com/book/post_slug
Route::get('/book/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// صفحه تگ‌ها - example.com/tags و example.com/tag/tag_slug
Route::get('/tags', [BlogController::class, 'tags'])->name('blog.tags');
Route::get('/tag/{tag:slug}', [BlogController::class, 'tag'])->name('blog.tag');

// صفحه ناشرها - example.com/publishers و example.com/publisher/publisher_slug
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
Route::prefix('sitemap')->name('sitemap.')->group(function () {
    Route::get('/', [SitemapController::class, 'index'])->name('index');
    Route::get('/posts', [SitemapController::class, 'posts'])->name('posts');
    Route::get('/categories', [SitemapController::class, 'categories'])->name('categories');
    Route::get('/authors', [SitemapController::class, 'authors'])->name('authors');
    Route::get('/publishers', [SitemapController::class, 'publishers'])->name('publishers');
    Route::get('/tags', [SitemapController::class, 'tags'])->name('tags');
});

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
