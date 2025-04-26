<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\PublisherController;
use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

// روت اصلی به بلاگ منتقل شده است
Route::get('/', [BlogController::class, 'index'])->name('blog.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // روت‌های مدیریت (پنل ادمین) - فقط مدیران دسترسی دارند
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('authors', AuthorController::class);
        Route::resource('publishers', PublisherController::class);

        // روت‌های اضافی برای مدیریت تصاویر
        Route::delete('post-images/{image}', [PostController::class, 'destroyImage'])->name('post-images.destroy');
        Route::post('post-images/reorder', [PostController::class, 'reorderImages'])->name('post-images.reorder');
    });
});

// در بخش روت‌های بلاگ
Route::get('/tag/{tag:slug}', [BlogController::class, 'tag'])->name('tag');

// روت‌های بلاگ با ساختار جدید
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/post/{post:slug}', [BlogController::class, 'show'])->name('show');
    Route::get('/category/{category:slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/categories', [BlogController::class, 'categories'])->name('categories');
    Route::get('/author/{author:slug}', [BlogController::class, 'author'])->name('author');
    Route::get('/publisher/{publisher:slug}', [BlogController::class, 'publisher'])->name('publisher');
    Route::get('/search', [BlogController::class, 'search'])->name('search');
});

require __DIR__.'/auth.php';
