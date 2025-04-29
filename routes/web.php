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

// Blog main page
Route::get('/', [BlogController::class, 'index'])->name('blog.index');

// Dashboard (auth + verified)
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated user routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Admin Panel (only for admins)
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

// Blog Routes
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/post/{post:slug}', [BlogController::class, 'show'])->name('show');
    Route::get('/category/{category:slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/categories', [BlogController::class, 'categories'])->name('categories');
    Route::get('/author/{author:slug}', [BlogController::class, 'author'])->name('author');
    Route::get('/publisher/{publisher:slug}', [BlogController::class, 'publisher'])->name('publisher');
    Route::get('/tag/{tag:slug}', [BlogController::class, 'tag'])->name('tag');
    Route::get('/search', [BlogController::class, 'search'])->name('search');
});

// Sitemap Routes
Route::prefix('sitemap')->name('sitemap.')->group(function () {
    Route::get('/', [SitemapController::class, 'index'])->name('index');
    Route::get('/posts', [SitemapController::class, 'posts'])->name('posts');
    Route::get('/categories', [SitemapController::class, 'categories'])->name('categories');
    Route::get('/authors', [SitemapController::class, 'authors'])->name('authors');
    Route::get('/publishers', [SitemapController::class, 'publishers'])->name('publishers');
    Route::get('/tags', [SitemapController::class, 'tags'])->name('tags');
});

// RSS Feed Routes
Route::prefix('feed')->name('feed.')->group(function () {
    Route::get('/', [RssController::class, 'index'])->name('index');
    Route::get('/category/{category:slug}', [RssController::class, 'category'])->name('category');
    Route::get('/author/{author:slug}', [RssController::class, 'author'])->name('author');
    Route::get('/tag/{tag:slug}', [RssController::class, 'tag'])->name('tag');
});

// Auth Routes
require __DIR__.'/auth.php';
