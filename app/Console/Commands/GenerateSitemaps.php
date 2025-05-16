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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemaps extends Command
{
    protected $signature = 'sitemap:generate {--type=all} {--force} {--chunk=1000}';
    protected $description = 'تولید فایل‌های نقشه سایت برای وب‌سایت';

    protected $itemsPerSitemap;
    protected $storageDir = 'public/sitemaps';
    protected $baseUrl;
    protected $now;

    /**
     * اجرای دستور
     */
    public function handle()
    {
        $startTime = microtime(true);
        $type = $this->option('type');
        $force = $this->option('force');
        $this->itemsPerSitemap = (int) $this->option('chunk');
        $this->baseUrl = config('app.url');
        $this->now = Carbon::now()->toAtomString();

        Storage::makeDirectory($this->storageDir);

        if ($force) {
            $this->clearSitemapFiles();
        }

        $this->info('شروع تولید نقشه سایت...');

        $generators = [
            'pages' => 'generatePagesSitemap',
            'posts' => 'generatePostsSitemap',
            'images' => 'generatePostImagesSitemap',
            'categories' => 'generateCategoriesSitemap',
            'authors' => 'generateAuthorsSitemap',
            'publishers' => 'generatePublishersSitemap',
            'tags' => 'generateTagsSitemap'
        ];

        foreach ($generators as $key => $method) {
            if ($type === 'all' || $type === $key) {
                $this->info("تولید نقشه سایت {$key}...");
                $this->$method();
            }
        }

        if ($type === 'all') {
            $this->info('تولید ایندکس اصلی نقشه سایت...');
            $this->generateMainIndex();
        }

        $executionTime = microtime(true) - $startTime;
        $this->info('تولید نقشه سایت در ' . number_format($executionTime, 2) . ' ثانیه به پایان رسید!');

        return 0;
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

        $sitemap->writeToFile(Storage::path("{$this->storageDir}/sitemap-pages.xml"));

        $pagesIndex = SitemapIndex::create();
        $pagesIndex->add("{$this->baseUrl}/sitemap-pages.xml");
        $pagesIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-pages-index.xml"));
    }

    /**
     * تولید سایت‌مپ پست‌ها
     */
    protected function generatePostsSitemap()
    {
        $totalPosts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->count();

        $totalSitemaps = ceil($totalPosts / $this->itemsPerSitemap);
        $this->info("تعداد کل پست‌ها: {$totalPosts} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        $sitemapIndex = SitemapIndex::create();

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

        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-posts.xml"));
    }

    /**
     * تولید سایت‌مپ تصاویر پست‌ها
     */
    protected function generatePostImagesSitemap()
    {
        $totalImages = DB::table('post_images')
            ->join('posts', 'posts.id', '=', 'post_images.post_id')
            ->where('posts.is_published', true)
            ->where('posts.hide_content', false)
            ->where(function($query) {
                $query->whereNull('post_images.hide_image')
                    ->orWhere('post_images.hide_image', 'visible');
            })
            ->count();

        $totalSitemaps = ceil($totalImages / $this->itemsPerSitemap);
        $this->info("تعداد کل تصاویر: {$totalImages} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        $sitemapIndex = SitemapIndex::create();

        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ تصاویر - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();
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

            $this->processImages($sitemap, $images);

            $fileName = "sitemap-post-images-{$page}.xml";
            $sitemap->writeToFile(Storage::path("{$this->storageDir}/{$fileName}"));
            $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
        }

        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-post-images.xml"));
    }

    /**
     * پردازش تصاویر برای سایت‌مپ
     */
    protected function processImages($sitemap, $images)
    {
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
    }

    /**
     * تولید سایت‌مپ دسته‌بندی‌ها
     */
    protected function generateCategoriesSitemap()
    {
        $totalCategories = Category::count();
        $totalSitemaps = ceil($totalCategories / $this->itemsPerSitemap);
        $this->info("تعداد کل دسته‌بندی‌ها: {$totalCategories} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        $sitemapIndex = SitemapIndex::create();

        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ دسته‌بندی‌ها - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();
            $categories = Category::select(['id', 'slug', 'updated_at', 'created_at', 'posts_count'])
                ->orderBy('id')
                ->offset(($page - 1) * $this->itemsPerSitemap)
                ->limit($this->itemsPerSitemap)
                ->get();

            foreach ($categories as $category) {
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

        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-categories.xml"));
    }

    /**
     * تولید سایت‌مپ نویسندگان
     */
    protected function generateAuthorsSitemap()
    {
        $totalAuthors = Author::count();
        $totalSitemaps = ceil($totalAuthors / $this->itemsPerSitemap);
        $this->info("تعداد کل نویسندگان: {$totalAuthors} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        $sitemapIndex = SitemapIndex::create();

        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ نویسندگان - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();
            $authors = Author::select(['id', 'slug', 'updated_at', 'created_at', 'posts_count', 'coauthored_count'])
                ->orderBy('id')
                ->offset(($page - 1) * $this->itemsPerSitemap)
                ->limit($this->itemsPerSitemap)
                ->get();

            foreach ($authors as $author) {
                $totalPostsCount = $author->posts_count + $author->coauthored_count;
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

        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-authors.xml"));
    }

    /**
     * تولید سایت‌مپ ناشران
     */
    protected function generatePublishersSitemap()
    {
        $totalPublishers = Publisher::count();
        $totalSitemaps = ceil($totalPublishers / $this->itemsPerSitemap);
        $this->info("تعداد کل ناشران: {$totalPublishers} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        $sitemapIndex = SitemapIndex::create();

        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ ناشران - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();
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

        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-publishers.xml"));
    }

    /**
     * تولید سایت‌مپ برچسب‌ها
     */
    protected function generateTagsSitemap()
    {
        $totalTags = Tag::count();
        $totalSitemaps = ceil($totalTags / $this->itemsPerSitemap);
        $this->info("تعداد کل برچسب‌ها: {$totalTags} - تعداد سایت‌مپ‌ها: {$totalSitemaps}");

        $sitemapIndex = SitemapIndex::create();

        for ($page = 1; $page <= $totalSitemaps; $page++) {
            $this->info("تولید سایت‌مپ برچسب‌ها - صفحه {$page} از {$totalSitemaps}");

            $sitemap = Sitemap::create();
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

        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap-tags.xml"));
    }

    /**
     * تولید ایندکس اصلی سایت‌مپ
     */
    protected function generateMainIndex()
    {
        $sitemapIndex = SitemapIndex::create();
        $sitemapTypes = ['pages', 'posts', 'post-images', 'categories', 'authors', 'publishers', 'tags'];

        foreach ($sitemapTypes as $type) {
            $fileName = "sitemap-{$type}.xml";
            if (Storage::exists("{$this->storageDir}/{$fileName}")) {
                $sitemapIndex->add("{$this->baseUrl}/{$fileName}");
            }
        }

        $sitemapIndex->writeToFile(Storage::path("{$this->storageDir}/sitemap.php"));
        Storage::copy("{$this->storageDir}/sitemap.php", 'public/sitemap.php');
    }

    /**
     * محاسبه اولویت بر اساس تعداد پست‌ها
     */
    protected function calculatePriorityByCount($count)
    {
        if ($count > 100) return 0.9;
        if ($count > 50) return 0.8;
        if ($count > 20) return 0.7;
        if ($count > 10) return 0.6;
        if ($count > 0) return 0.5;
        return 0.4;
    }

    /**
     * بررسی مخفی بودن تصویر
     */
    protected function isImageHidden($image)
    {
        if (!is_object($image)) return true;

        if (method_exists($image, 'isHidden')) {
            return $image->isHidden();
        }

        return $image->hide_image === 'hidden';
    }

    /**
     * تبدیل مسیر تصویر به URL کامل
     */
    protected function getImageUrl($imagePath)
    {
        if (empty($imagePath)) return null;

        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            return $imagePath;
        }

        if (strpos($imagePath, 'images.balyan.ir/') !== false) {
            return 'https://' . $imagePath;
        }

        if (strpos($imagePath, 'post_images/') === 0 || strpos($imagePath, 'posts/') === 0) {
            return config('app.custom_image_host', 'https://images.balyan.ir') . '/' . $imagePath;
        }

        return $this->baseUrl . '/storage/' . $imagePath;
    }

    /**
     * پاک کردن فایل‌های سایت‌مپ قبلی
     */
    protected function clearSitemapFiles()
    {
        $this->info('در حال پاک کردن کش نقشه سایت...');

        $files = Storage::files($this->storageDir);
        foreach ($files as $file) {
            if (strpos($file, 'sitemap') !== false) {
                Storage::delete($file);
            }
        }

        if (Storage::exists('public/sitemap.php')) {
            Storage::delete('public/sitemap.php');
        }

        $this->info('فایل‌های سایت‌مپ قبلی پاک شدند.');
    }
}
