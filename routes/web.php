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
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\RssController;

// وبلاگ / صفحه اصلی - example.com
Route::get('/', [BlogController::class, 'index'])->name('blog.index');

// داشبورد (با احراز هویت + تأیید ایمیل)
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// مسیر دیباگ برای بررسی مسیرها
Route::get('/debug/routes', function () {
    $routes = [];
    foreach (Route::getRoutes() as $route) {
        if (strpos($route->getName(), 'publisher') !== false || strpos($route->uri, 'publisher') !== false) {
            $routes[] = [
                'method' => implode('|', $route->methods),
                'uri' => $route->uri,
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        }
    }

    return response()->json($routes);
});

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
        // مسیر ویرایش سریع برای پست 772083
        Route::get('/posts/772083/edit', function() {
            try {
                // بارگذاری داده‌های پست با کوئری خام بهینه‌سازی شده
                $postData = DB::table('posts')
                    ->where('id', 772083)
                    ->select([
                        'id', 'title', 'english_title', 'slug', 'content', 'english_content',
                        'category_id', 'author_id', 'publisher_id', 'language',
                        'publication_year', 'format', 'book_codes', 'purchase_link',
                        'is_published', 'hide_content'
                    ])
                    ->first();

                if (!$postData) {
                    return redirect()->route('admin.posts.index')
                        ->with('error', 'پست مورد نظر یافت نشد.');
                }

                // بارگذاری تصویر شاخص با لود تأخیری
                $featuredImage = Cache::remember("post_772083_featured_image", 3600, function() {
                    return DB::table('post_images')
                        ->where('post_id', 772083)
                        ->select(['id', 'post_id', 'image_path', 'hide_image'])
                        ->orderBy('sort_order')
                        ->first();
                });

                // بارگذاری لیست‌های دسته‌بندی‌ها، نویسندگان و ناشران با کش
                $categories = Cache::remember('admin_categories_list', 3600, function() {
                    return DB::table('categories')
                        ->select(['id', 'name'])
                        ->orderBy('name')
                        ->get();
                });

                $authors = Cache::remember('admin_authors_list', 3600, function() {
                    return DB::table('authors')
                        ->select(['id', 'name'])
                        ->orderBy('name')
                        ->get();
                });

                $publishers = Cache::remember('admin_publishers_list', 3600, function() {
                    return DB::table('publishers')
                        ->select(['id', 'name'])
                        ->orderBy('name')
                        ->get();
                });

                // تبدیل پست به یک آبجکت برای استفاده راحت‌تر در ویو
                $post = (object)$postData;

                // نمایش ویو edit با داده‌های بهینه‌سازی شده
                return view('admin.posts.edit', compact('post', 'categories', 'authors', 'publishers', 'featuredImage'));

            } catch (\Exception $e) {
                report($e);
                return redirect()->route('admin.posts.index')
                    ->with('error', 'خطایی در بارگذاری فرم ویرایش رخ داد: ' . $e->getMessage());
            }
        })->name('posts.edit-772083');

        // مسیرهای استاندارد دسته‌بندی‌ها، نویسندگان و پست‌ها
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('authors', AuthorController::class);

        // مسیرهای ناشر - به صورت صریح برای جلوگیری از مشکلات احتمالی
        Route::get('publishers', [PublisherController::class, 'index'])->name('publishers.index');
        Route::get('publishers/create', [PublisherController::class, 'create'])->name('publishers.create');
        Route::post('publishers', [PublisherController::class, 'store'])->name('publishers.store');
        Route::get('publishers/{publisher}/edit', [PublisherController::class, 'edit'])->name('publishers.edit');
        Route::put('publishers/{publisher}', [PublisherController::class, 'update'])->name('publishers.update');
        Route::delete('publishers/{publisher}', [PublisherController::class, 'destroy'])->name('publishers.destroy');

        // مسیرهای تصاویر پست
        Route::delete('post-images/{image}', [PostController::class, 'destroyImage'])->name('post-images.destroy');
        Route::post('post-images/reorder', [PostController::class, 'reorderImages'])->name('post-images.reorder');

        // گالری تصاویر - مسیرهای جدید
        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery');
        Route::get('api/gallery/images', [GalleryController::class, 'getImages']);
        Route::post('api/gallery/categorize', [GalleryController::class, 'categorizeImage']);

        // مسیرهای گالری تصاویر تایید شده و رد شده
        Route::get('gallery/visible', [GalleryController::class, 'visible'])->name('gallery.visible');
        Route::get('api/gallery/visible', [GalleryController::class, 'getVisibleImages']);
        Route::get('gallery/hidden', [GalleryController::class, 'hidden'])->name('gallery.hidden');
        Route::get('api/gallery/hidden', [GalleryController::class, 'getHiddenImages']);
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
