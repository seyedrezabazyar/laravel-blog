<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

// روت‌های پیش‌فرض
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // روت‌های مدیریت پست‌ها (پنل ادمین) - فقط مدیران دسترسی دارند
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class);
    });
});

// روت‌های عمومی بلاگ
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{category:slug}', [BlogController::class, 'category'])->name('blog.category');

require __DIR__.'/auth.php';
