<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Publisher;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemaps extends Command
{
    /**
     * نام و امضای دستور.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--type=all} {--force} {--chunk=1000}';

    /**
     * توضیحات دستور.
     *
     * @var string
     */
    protected $description = 'تولید فایل‌های نقشه سایت برای وب‌سایت';

    /**
     * تعداد آیتم‌ها در هر صفحه از سایت‌مپ
     *
     * @var int
     */
    protected $itemsPerSitemap;

    /**
     * مسیر ذخیره‌سازی سایت‌مپ‌ها
     *
     * @var string
     */
    protected $storageDir = 'public/sitemaps';

    /**
     * آدرس عمومی سایت
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * زمان فعلی برای آخرین به‌روزرسانی
     *
     * @var string
     */
    protected $now;

    /**
     * اجرای دستور.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $type = $this->option('type');
        $force = $this->option('force');
        $this->itemsPerSitemap = (int) $this->option('chunk');
        $this->baseUrl = config('app.url');
        $this->now = Carbon::now()->toAtomString();

        // ایجاد دایرکتوری ذخیره‌سازی اگر وجود نداشته باشد
        Storage::makeDirectory($this->storageDir);

        // پاک کردن کش در صورت اجبار
        if ($force) {
            $this->info('در حال پاک کردن کش نقشه سایت...');
            $this->clearSitemapFiles();
        }

        $this->info('شروع تولید نقشه سایت...');

        // تولید سایت‌مپ‌های مناسب بر اساس نوع
        if ($type === 'all' || $type === 'pages') {
            $this->info('تولید نقشه سایت صفحات استاتیک...');
            $this->generatePagesSitemap();
        }

        if ($type === 'all' || $type === 'posts') {
            $this->info('تولید نقشه سایت پست‌ها...');
            $this->generatePostsSitemap();
        }

        if ($type === 'all' || $type === 'images') {
            $this->info('تولید نقشه سایت تصاویر پست‌ها...');
            $this->generatePostImagesSitemap();
        }

        if ($type === 'all' || $type === 'categories') {
            $this->info('تولید نقشه سایت دسته‌بندی‌ها...');
            $this->generateCategoriesSitemap();
        }

        if ($type === 'all' || $type === 'authors') {
            $this->info('تولید نقشه سایت نویسندگان...');
            $this->generateAuthorsSitemap();
        }

        if ($type === 'all' || $type === 'publishers') {
            $this->info('تولید نقشه سایت ناشران...');
            $this->generatePublishersSitemap();
        }

        if ($type === 'all' || $type === 'tags') {
            $this->info('تولید نقشه سایت برچسب‌ها...');
            $this->generateTagsSitemap();
        }

        // همیشه ایندکس اصلی را در آخر تولید می‌کنیم
        if ($type === 'all') {
            $this->info('تولید ایندکس اصلی نقشه سایت...');
            $this->generateMainIndex();
        }

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime);

        $this->info('تولید نقشه سایت در ' . number_format($executionTime, 2) . ' ثانیه به پایان رسید!');
    }

    /**
     * تولید سایت‌مپ صفحات استاتیک
     */
    protected function generatePagesSitemap()
    {
        $sitemap = Sitemap::create();

        // اضافه کردن صفحه اصلی
        $sitemap->add(Url::create('/')
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1.0));

        // افزودن صفحات استاتیک مهم
        $staticPages = [
            '/search' => ['weekly', 0.7],
            '/categories' => ['weekly', 0.8],
            '/authors' => ['weekly', 0.8],
            '/publishers' => ['weekly', 0.8],
            '/tags' => ['weekly', 0.8],
        ];

        foreach ($staticPages as $url => [$changeFreq, $priority]) {
            $sitemap->add(Url::create($url)
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency($changeFreq)
                ->setPriority($priority));
        }

        // ذخیره سایت‌مپ
        $sitemap->writeToFile(Storage::path("{$this->storageDir}/sitemap-pages.xml"));

        // ایجاد یک فایل شاخص برای صفحات استاتیک
        $pagesIndex = SitemapIndex::create();
        $pagesIndex->add("{$this->baseUrl}/sitemap-pages.xml");
        $pagesIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-pages-index.xml"));
    }

    /**
     * تولید سایت‌مپ پست‌ها
     */
    protected function generatePostsSitemap()
    {
        // دریافت تعداد کل پست‌ها
        $totalPosts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->count();

        // محاسبه تعداد صفحات سایت‌مپ
        $totalSitemaps = ceil($totalPosts / $this->itemsPerSitemap);
        $this->info("تعداد کل پست‌ها: {$totalPosts} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        // ایجاد فایل شاخص برای پست‌ها
        $sitemapIndex = SitemapIndex::create();

        // ایجاد سایت‌مپ برای هر صفحه
        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ پست‌ها - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();
            $posts = Post::where('is_published', true)
                ->where('hide_content', false)
                ->select(['id', 'slug', 'updated_at', 'created_at', 'title'])
                ->orderBy('id')
                ->offset(($page - 1) * $this->itemsPerSitemap)
                ->limit($this->itemsPerSitemap)
                ->get();

            foreach ($posts as $post) {
                $url = Url::create("/book/{$post->slug}")
                    ->setLastModificationDate($post->updated_at ?? $post->created_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.8);

                // اضافه کردن تگ تصویر اگر پست تصویر دارد
                $featuredImage = $post->featuredImage;
                if ($featuredImage && !$this->isImageHidden($featuredImage)) {
                    $imageUrl = $this->getImageUrl($featuredImage->image_path);

                    if ($imageUrl) {
                        $url->addImage($imageUrl, $post->title);
                    }
                }

                $sitemap->add($url);
            }

            $fileName = "sitemap-posts-{$page}.xml";
            $sitemap->writeToFile(Storage::path("{$this->storageDir}/{$fileName}"));
            $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
        }

        // ذخیره ایندکس سایت‌مپ پست‌ها
        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-posts.xml"));
    }

    /**
     * تولید سایت‌مپ تصاویر پست‌ها
     */
    protected function generatePostImagesSitemap()
    {
        // شمارش تعداد کل تصاویر قابل مشاهده
        $totalImages = DB::table('post_images')
            ->join('posts', 'posts.id', '=', 'post_images.post_id')
            ->where('posts.is_published', true)
            ->where('posts.hide_content', false)
            ->where(function($query) {
                $query->whereNull('post_images.hide_image')
                    ->orWhere('post_images.hide_image', 'visible');
            })
            ->count();

        // محاسبه تعداد صفحات سایت‌مپ
        $totalSitemaps = ceil($totalImages / $this->itemsPerSitemap);
        $this->info("تعداد کل تصاویر: {$totalImages} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        // ایجاد فایل شاخص برای تصاویر
        $sitemapIndex = SitemapIndex::create();

        // ایجاد سایت‌مپ برای هر صفحه
        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ تصاویر - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();

            // دریافت تصاویر برای این صفحه - بهینه‌سازی شده بدون N+1
            $images = DB::table('post_images')
                ->join('posts', 'posts.id', '=', 'post_images.post_id')
                ->select([
                    'post_images.id',
                    'post_images.image_path',
                    'post_images.caption',
                    'post_images.hide_image',
                    'post_images.updated_at',
                    'posts.slug as post_slug',
                    'posts.title as post_title'
                ])
                ->where('posts.is_published', true)
                ->where('posts.hide_content', false)
                ->where(function($query) {
                    $query->whereNull('post_images.hide_image')
                        ->orWhere('post_images.hide_image', 'visible');
                })
                ->orderBy('post_images.id')
                ->offset(($page - 1) * $this->itemsPerSitemap)
                ->limit($this->itemsPerSitemap)
                ->get();

            // گروه‌بندی تصاویر بر اساس پست برای کاهش تعداد URL
            $postImagesGroup = [];
            foreach ($images as $image) {
                if (!isset($postImagesGroup[$image->post_slug])) {
                    $postImagesGroup[$image->post_slug] = [
                        'title' => $image->post_title,
                        'updated_at' => $image->updated_at,
                        'images' => []
                    ];
                }

                $imageUrl = $this->getImageUrl($image->image_path);
                if ($imageUrl) {
                    $postImagesGroup[$image->post_slug]['images'][] = [
                        'url' => $imageUrl,
                        'caption' => $image->caption ?? $image->post_title
                    ];
                }
            }

            // ایجاد URL برای هر پست با تصاویرش
            foreach ($postImagesGroup as $postSlug => $data) {
                $url = Url::create("/book/{$postSlug}")
                    ->setLastModificationDate(Carbon::parse($data['updated_at'] ?? $this->now))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.7);

                foreach ($data['images'] as $image) {
                    $url->addImage($image['url'], $image['caption']);
                }

                $sitemap->add($url);
            }

            $fileName = "sitemap-post-images-{$page}.xml";
            $sitemap->writeToFile(Storage::path("{$this->storageDir}/{$fileName}"));
            $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
        }

        // ذخیره ایندکس سایت‌مپ تصاویر
        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-post-images.xml"));
    }

    /**
     * تولید سایت‌مپ دسته‌بندی‌ها
     */
    protected function generateCategoriesSitemap()
    {
        // دریافت تعداد کل دسته‌بندی‌ها
        $totalCategories = Category::count();

        // محاسبه تعداد صفحات سایت‌مپ
        $totalSitemaps = ceil($totalCategories / $this->itemsPerSitemap);
        $this->info("تعداد کل دسته‌بندی‌ها: {$totalCategories} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        // ایجاد فایل شاخص برای دسته‌بندی‌ها
        $sitemapIndex = SitemapIndex::create();

        // ایجاد سایت‌مپ برای هر صفحه
        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ دسته‌بندی‌ها - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();
            $categories = Category::select(['id', 'slug', 'updated_at', 'created_at', 'posts_count'])
                ->orderBy('id')
                ->offset(($page - 1) * $this->itemsPerSitemap)
                ->limit($this->itemsPerSitemap)
                ->get();

            foreach ($categories as $category) {
                // محاسبه اولویت بر اساس تعداد پست‌ها
                $priority = $this->calculatePriorityByCount($category->posts_count);

                $sitemap->add(Url::create("/category/{$category->slug}")
                    ->setLastModificationDate($category->updated_at ?? $category->created_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority($priority));
            }

            $fileName = "sitemap-categories-{$page}.xml";
            $sitemap->writeToFile(Storage::path("{$this->storageDir}/{$fileName}"));
            $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
        }

        // ذخیره ایندکس سایت‌مپ دسته‌بندی‌ها
        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-categories.xml"));
    }

    /**
     * تولید سایت‌مپ نویسندگان
     */
    protected function generateAuthorsSitemap()
    {
        // دریافت تعداد کل نویسندگان
        $totalAuthors = Author::count();

        // محاسبه تعداد صفحات سایت‌مپ
        $totalSitemaps = ceil($totalAuthors / $this->itemsPerSitemap);
        $this->info("تعداد کل نویسندگان: {$totalAuthors} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        // ایجاد فایل شاخص برای نویسندگان
        $sitemapIndex = SitemapIndex::create();

        // ایجاد سایت‌مپ برای هر صفحه
        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ نویسندگان - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();
            $authors = Author::select(['id', 'slug', 'updated_at', 'created_at', 'posts_count', 'coauthored_count'])
                ->orderBy('id')
                ->offset(($page - 1) * $this->itemsPerSitemap)
                ->limit($this->itemsPerSitemap)
                ->get();

            foreach ($authors as $author) {
                // محاسبه تعداد کل پست‌ها
                $totalPostsCount = $author->posts_count + $author->coauthored_count;

                // محاسبه اولویت بر اساس تعداد پست‌ها
                $priority = $this->calculatePriorityByCount($totalPostsCount);

                $sitemap->add(Url::create("/author/{$author->slug}")
                    ->setLastModificationDate($author->updated_at ?? $author->created_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority($priority));
            }

            $fileName = "sitemap-authors-{$page}.xml";
            $sitemap->writeToFile(Storage::path("{$this->storageDir}/{$fileName}"));
            $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
        }

        // ذخیره ایندکس سایت‌مپ نویسندگان
        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-authors.xml"));
    }

    /**
     * تولید سایت‌مپ ناشران
     */
    protected function generatePublishersSitemap()
    {
        // دریافت تعداد کل ناشران
        $totalPublishers = Publisher::count();

        // محاسبه تعداد صفحات سایت‌مپ
        $totalSitemaps = ceil($totalPublishers / $this->itemsPerSitemap);
        $this->info("تعداد کل ناشران: {$totalPublishers} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        // ایجاد فایل شاخص برای ناشران
        $sitemapIndex = SitemapIndex::create();

        // ایجاد سایت‌مپ برای هر صفحه
        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ ناشران - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();

            // کوئری با شمارنده پست‌ها
            $publishers = Publisher::select(['id', 'slug', 'updated_at', 'created_at'])
                ->withCount(['posts' => function($query) {
                    $query->where('is_published', true)
                        ->where('hide_content', false);
                }])
                ->orderBy('id')
                ->offset(($page - 1) * $this->itemsPerSitemap)
                ->limit($this->itemsPerSitemap)
                ->get();

            foreach ($publishers as $publisher) {
                // محاسبه اولویت بر اساس تعداد پست‌ها
                $priority = $this->calculatePriorityByCount($publisher->posts_count);

                $sitemap->add(Url::create("/publisher/{$publisher->slug}")
                    ->setLastModificationDate($publisher->updated_at ?? $publisher->created_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority($priority));
            }

            $fileName = "sitemap-publishers-{$page}.xml";
            $sitemap->writeToFile(Storage::path("{$this->storageDir}/{$fileName}"));
            $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
        }

        // ذخیره ایندکس سایت‌مپ ناشران
        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-publishers.xml"));
    }

    /**
     * تولید سایت‌مپ برچسب‌ها
     */
    protected function generateTagsSitemap()
    {
        // دریافت تعداد کل برچسب‌ها
        $totalTags = Tag::count();

        // محاسبه تعداد صفحات سایت‌مپ
        $totalSitemaps = ceil($totalTags / $this->itemsPerSitemap);
        $this->info("تعداد کل برچسب‌ها: {$totalTags} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        // ایجاد فایل شاخص برای برچسب‌ها
        $sitemapIndex = SitemapIndex::create();

        // ایجاد سایت‌مپ برای هر صفحه
        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ برچسب‌ها - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();

            // کوئری با شمارنده پست‌ها
            $tags = Tag::select(['id', 'slug', 'updated_at', 'created_at'])
                ->withCount(['posts' => function($query) {
                    $query->where('is_published', true)
                        ->where('hide_content', false);
                }])
                ->orderBy('id')
                ->offset(($page - 1) * $this->itemsPerSitemap)
                ->limit($this->itemsPerSitemap)
                ->get();

            foreach ($tags as $tag) {
                // محاسبه اولویت بر اساس تعداد پست‌ها
                $priority = $this->calculatePriorityByCount($tag->posts_count);

                $sitemap->add(Url::create("/tag/{$tag->slug}")
                    ->setLastModificationDate($tag->updated_at ?? $tag->created_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority($priority));
            }

            $fileName = "sitemap-tags-{$page}.xml";
            $sitemap->writeToFile(Storage::path("{$this->storageDir}/{$fileName}"));
            $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
        }

        // ذخیره ایندکس سایت‌مپ برچسب‌ها
        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-tags.xml"));
    }

    /**
     * تولید ایندکس اصلی سایت‌مپ
     */
    protected function generateMainIndex()
    {
        $sitemapIndex = SitemapIndex::create();

        // اضافه کردن ایندکس‌های موجود
        $sitemapTypes = [
            'pages',
            'posts',
            'post-images',
            'categories',
            'authors',
            'publishers',
            'tags'
        ];

        foreach ($sitemapTypes as $type) {
            $fileName = "sitemap-{$type}.xml";
            if (Storage::exists("{$this->storageDir}/{$fileName}")) {
                $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
            }
        }

        // ذخیره ایندکس اصلی
        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap.php"));

        // کپی کردن به مسیر اصلی برای دسترسی آسان
        Storage::copy("{$this->storageDir}/sitemap.php", 'public/sitemap.php');
    }

    /**
     * محاسبه اولویت بر اساس تعداد پست‌ها یا اهمیت
     *
     * @param int $count
     * @return float
     */
    protected function calculatePriorityByCount($count)
    {
        if ($count > 100) {
            return 0.9;
        } elseif ($count > 50) {
            return 0.8;
        } elseif ($count > 20) {
            return 0.7;
        } elseif ($count > 10) {
            return 0.6;
        } elseif ($count > 0) {
            return 0.5;
        }

        return 0.4;
    }

    /**
     * بررسی مخفی بودن تصویر
     *
     * @param PostImage|object $image
     * @return bool
     */
    protected function isImageHidden($image)
    {
        if (is_object($image)) {
            // اگر مدل PostImage باشد از متد isHidden استفاده می‌کنیم
            if (method_exists($image, 'isHidden')) {
                return $image->isHidden();
            }

            // اگر یک آبجکت عادی باشد (مثلاً نتیجه کوئری)
            return $image->hide_image === 'hidden';
        }

        return true;
    }

    /**
     * تبدیل مسیر تصویر به URL کامل
     *
     * @param string|null $imagePath
     * @return string|null
     */
    protected function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }

        // URL مستقیم برای HTTP/HTTPS
        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            return $imagePath;
        }

        // برای دامنه images.balyan.ir
        if (strpos($imagePath, 'images.balyan.ir/') !== false) {
            return 'https://' . $imagePath;
        }

        // برای تصاویر هاست دانلود
        if (strpos($imagePath, 'post_images/') === 0 || strpos($imagePath, 'posts/') === 0) {
            return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $imagePath;
        }

        // برای استوریج محلی
        return $this->baseUrl . '/storage/' . $imagePath;
    }

    /**
     * پاک کردن فایل‌های سایت‌مپ قبلی
     */
    protected function clearSitemapFiles()
    {
        $files = Storage::files($this->storageDir);

        foreach ($files as $file) {
            if (strpos($file, 'sitemap') !== false) {
                Storage::delete($file);
            }
        }

        // حذف سایت‌مپ اصلی از مسیر عمومی
        if (Storage::exists('public/sitemap.php')) {
            Storage::delete('public/sitemap.php');
        }

        $this->info('فایل‌های سایت‌مپ قبلی پاک شدند.');
    }
}
