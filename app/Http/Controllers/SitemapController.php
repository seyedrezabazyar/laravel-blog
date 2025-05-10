<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * مدت زمان کش به ثانیه (7 روز)
     */
    protected $cacheTtl = 604800;

    /**
     * تعداد آیتم‌ها در هر سایت‌مپ - مانند وردپرس
     */
    protected $itemsPerSitemap = 1000;

    /**
     * تاریخ آخرین بروزرسانی
     */
    protected $lastmod;

    /**
     * میزبان سایت
     */
    protected $host;

    /**
     * مقدار پیش‌فرض changefreq
     */
    protected $defaultChangefreq = 'weekly';

    /**
     * مقدار پیش‌فرض priority
     */
    protected $defaultPriority = 0.7;

    /**
     * سازنده کلاس
     */
    public function __construct()
    {
        $this->lastmod = Carbon::now()->startOfDay()->toAtomString();
        $this->host = request()->getSchemeAndHttpHost();
    }

    /**
     * نمایش صفحه اصلی سایت‌مپ (شاخص)
     */
    public function index()
    {
        return Cache::remember('sitemap_index', $this->cacheTtl, function () {
            // محاسبه تعداد سایت‌مپ‌های مورد نیاز برای هر نوع محتوا
            $postSitemapCount = $this->getPostSitemapCount();
            $categorySitemapCount = $this->getCategorySitemapCount();
            $authorSitemapCount = $this->getAuthorSitemapCount();
            $publisherSitemapCount = $this->getPublisherSitemapCount();
            $tagSitemapCount = $this->getTagSitemapCount();

            // ایجاد شاخص سایت‌مپ
            $sitemapIndex = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $sitemapIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

            // اضافه کردن سایت‌مپ صفحه اصلی
            $sitemapIndex .= $this->getSitemapIndexEntry('sitemap-home.xml');

            // سایت‌مپ پست‌ها
            for ($i = 1; $i <= $postSitemapCount; $i++) {
                $sitemapIndex .= $this->getSitemapIndexEntry("sitemap-posts-{$i}.xml");
            }

            // سایت‌مپ دسته‌بندی‌ها
            for ($i = 1; $i <= $categorySitemapCount; $i++) {
                $sitemapIndex .= $this->getSitemapIndexEntry("sitemap-categories-{$i}.xml");
            }

            // سایت‌مپ نویسندگان
            for ($i = 1; $i <= $authorSitemapCount; $i++) {
                $sitemapIndex .= $this->getSitemapIndexEntry("sitemap-authors-{$i}.xml");
            }

            // سایت‌مپ ناشران
            for ($i = 1; $i <= $publisherSitemapCount; $i++) {
                $sitemapIndex .= $this->getSitemapIndexEntry("sitemap-publishers-{$i}.xml");
            }

            // سایت‌مپ تگ‌ها
            for ($i = 1; $i <= $tagSitemapCount; $i++) {
                $sitemapIndex .= $this->getSitemapIndexEntry("sitemap-tags-{$i}.xml");
            }

            $sitemapIndex .= '</sitemapindex>';

            return response($sitemapIndex, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl);
        });
    }

    /**
     * ایجاد یک فیلد برای فایل شاخص سایت‌مپ
     */
    protected function getSitemapIndexEntry($filename)
    {
        return "\t<sitemap>\n\t\t<loc>{$this->host}/{$filename}</loc>\n\t\t<lastmod>{$this->lastmod}</lastmod>\n\t</sitemap>\n";
    }

    /**
     * نمایش سایت‌مپ صفحه اصلی
     */
    public function home()
    {
        return Cache::remember('sitemap_home', $this->cacheTtl, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
            $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

            // صفحه اصلی
            $xml .= $this->getUrlXml(URL::to('/'), $this->lastmod, 'daily', '1.0');

            // لینک صفحات استاتیک مهم
            $xml .= $this->getUrlXml(route('blog.categories'), $this->lastmod, 'weekly', '0.8');
            $xml .= $this->getUrlXml(route('blog.search') . '?q=popular', $this->lastmod, 'weekly', '0.6');

            $xml .= '</urlset>';

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl);
        });
    }

    /**
     * نمایش سایت‌مپ پست‌ها
     */
    public function posts($page = 1)
    {
        return Cache::remember("sitemap_posts_page_{$page}", $this->cacheTtl, function () use ($page) {
            // دریافت پست‌های این صفحه
            $posts = $this->getPostsForPage($page);

            if ($posts->isEmpty()) {
                abort(404);
            }

            // شروع XML
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
            $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ';
            $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

            // دریافت تصاویر برای همه پست‌ها در یک کوئری
            $postIds = $posts->pluck('id')->toArray();
            $imageMap = $this->getPostImagesMap($postIds);

            // اضافه کردن URL برای هر پست
            foreach ($posts as $post) {
                $lastmod = $post->updated_at ? $post->updated_at->toAtomString() : $this->lastmod;
                $url = route('blog.show', $post->slug);
                $priority = $this->calculatePostPriority($post);

                $xml .= "\t<url>\n";
                $xml .= "\t\t<loc>{$url}</loc>\n";
                $xml .= "\t\t<lastmod>{$lastmod}</lastmod>\n";
                $xml .= "\t\t<changefreq>{$this->defaultChangefreq}</changefreq>\n";
                $xml .= "\t\t<priority>{$priority}</priority>\n";

                // اضافه کردن تصویر اگر وجود داشته باشد
                if (isset($imageMap[$post->id])) {
                    $imageUrl = $imageMap[$post->id]['url'];
                    $title = htmlspecialchars($post->title, ENT_XML1);
                    $xml .= "\t\t<image:image>\n";
                    $xml .= "\t\t\t<image:loc>{$imageUrl}</image:loc>\n";
                    $xml .= "\t\t\t<image:title>{$title}</image:title>\n";
                    $xml .= "\t\t</image:image>\n";
                }

                $xml .= "\t</url>\n";
            }

            $xml .= '</urlset>';

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl);
        });
    }

    /**
     * نمایش سایت‌مپ دسته‌بندی‌ها
     */
    public function categories($page = 1)
    {
        return Cache::remember("sitemap_categories_page_{$page}", $this->cacheTtl, function () use ($page) {
            // دریافت دسته‌بندی‌های این صفحه
            $categories = $this->getCategoriesForPage($page);

            if ($categories->isEmpty()) {
                abort(404);
            }

            // شروع XML
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
            $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

            // اضافه کردن URL برای هر دسته‌بندی
            foreach ($categories as $category) {
                $url = route('blog.category', $category->slug);
                $priority = $this->calculateCategoryPriority($category);

                $xml .= $this->getUrlXml($url, $this->lastmod, $this->defaultChangefreq, $priority);
            }

            $xml .= '</urlset>';

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl);
        });
    }

    /**
     * نمایش سایت‌مپ نویسندگان
     */
    public function authors($page = 1)
    {
        return Cache::remember("sitemap_authors_page_{$page}", $this->cacheTtl, function () use ($page) {
            // دریافت نویسندگان این صفحه
            $authors = $this->getAuthorsForPage($page);

            if ($authors->isEmpty()) {
                abort(404);
            }

            // شروع XML
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
            $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

            // اضافه کردن URL برای هر نویسنده
            foreach ($authors as $author) {
                $url = route('blog.author', $author->slug);
                $priority = $this->calculateAuthorPriority($author);

                $xml .= $this->getUrlXml($url, $this->lastmod, $this->defaultChangefreq, $priority);
            }

            $xml .= '</urlset>';

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl);
        });
    }

    /**
     * نمایش سایت‌مپ ناشران
     */
    public function publishers($page = 1)
    {
        return Cache::remember("sitemap_publishers_page_{$page}", $this->cacheTtl, function () use ($page) {
            // دریافت ناشران این صفحه
            $publishers = $this->getPublishersForPage($page);

            if ($publishers->isEmpty()) {
                abort(404);
            }

            // شروع XML
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
            $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

            // اضافه کردن URL برای هر ناشر
            foreach ($publishers as $publisher) {
                $url = route('blog.publisher', $publisher->slug);
                $priority = $this->calculatePublisherPriority($publisher);

                $xml .= $this->getUrlXml($url, $this->lastmod, $this->defaultChangefreq, $priority);
            }

            $xml .= '</urlset>';

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl);
        });
    }

    /**
     * نمایش سایت‌مپ تگ‌ها
     */
    public function tags($page = 1)
    {
        return Cache::remember("sitemap_tags_page_{$page}", $this->cacheTtl, function () use ($page) {
            // دریافت تگ‌های این صفحه
            $tags = $this->getTagsForPage($page);

            if ($tags->isEmpty()) {
                abort(404);
            }

            // شروع XML
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
            $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

            // اضافه کردن URL برای هر تگ
            foreach ($tags as $tag) {
                $url = route('blog.tag', $tag->slug);
                $priority = $this->calculateTagPriority($tag);

                $xml .= $this->getUrlXml($url, $this->lastmod, $this->defaultChangefreq, $priority);
            }

            $xml .= '</urlset>';

            return response($xml, 200)
                ->header('Content-Type', 'application/xml; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl);
        });
    }

    /**
     * ایجاد بخش URL در XML
     */
    protected function getUrlXml($loc, $lastmod, $changefreq, $priority)
    {
        return "\t<url>\n\t\t<loc>{$loc}</loc>\n\t\t<lastmod>{$lastmod}</lastmod>\n\t\t<changefreq>{$changefreq}</changefreq>\n\t\t<priority>{$priority}</priority>\n\t</url>\n";
    }

    /**
     * محاسبه تعداد سایت‌مپ‌های پست
     */
    protected function getPostSitemapCount()
    {
        return Cache::remember('post_sitemap_count', $this->cacheTtl, function() {
            $count = Post::where('is_published', true)
                ->where('hide_content', false)
                ->count();

            return max(1, ceil($count / $this->itemsPerSitemap));
        });
    }

    /**
     * محاسبه تعداد سایت‌مپ‌های دسته‌بندی
     */
    protected function getCategorySitemapCount()
    {
        return Cache::remember('category_sitemap_count', $this->cacheTtl, function() {
            $count = Category::count();
            return max(1, ceil($count / $this->itemsPerSitemap));
        });
    }

    /**
     * محاسبه تعداد سایت‌مپ‌های نویسنده
     */
    protected function getAuthorSitemapCount()
    {
        return Cache::remember('author_sitemap_count', $this->cacheTtl, function() {
            $count = Author::count();
            return max(1, ceil($count / $this->itemsPerSitemap));
        });
    }

    /**
     * محاسبه تعداد سایت‌مپ‌های ناشر
     */
    protected function getPublisherSitemapCount()
    {
        return Cache::remember('publisher_sitemap_count', $this->cacheTtl, function() {
            $count = Publisher::count();
            return max(1, ceil($count / $this->itemsPerSitemap));
        });
    }

    /**
     * محاسبه تعداد سایت‌مپ‌های تگ
     */
    protected function getTagSitemapCount()
    {
        return Cache::remember('tag_sitemap_count', $this->cacheTtl, function() {
            $count = Tag::count();
            return max(1, ceil($count / $this->itemsPerSitemap));
        });
    }

    /**
     * دریافت پست‌های صفحه مشخص
     */
    protected function getPostsForPage($page)
    {
        $offset = ($page - 1) * $this->itemsPerSitemap;

        return Post::where('is_published', true)
            ->where('hide_content', false)
            ->select(['id', 'slug', 'title', 'updated_at', 'created_at', 'publication_year'])
            ->orderBy('id')
            ->skip($offset)
            ->take($this->itemsPerSitemap)
            ->get();
    }

    /**
     * دریافت دسته‌بندی‌های صفحه مشخص
     */
    protected function getCategoriesForPage($page)
    {
        $offset = ($page - 1) * $this->itemsPerSitemap;

        return Category::select(['id', 'slug', 'posts_count'])
            ->orderBy('id')
            ->skip($offset)
            ->take($this->itemsPerSitemap)
            ->get();
    }

    /**
     * دریافت نویسندگان صفحه مشخص
     */
    protected function getAuthorsForPage($page)
    {
        $offset = ($page - 1) * $this->itemsPerSitemap;

        return DB::table('authors')
            ->select('id', 'slug')
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.author_id = authors.id AND posts.is_published = 1 AND posts.hide_content = 0) as posts_count'))
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM post_author JOIN posts ON posts.id = post_author.post_id WHERE post_author.author_id = authors.id AND posts.is_published = 1 AND posts.hide_content = 0) as coauthored_count'))
            ->orderBy('id')
            ->skip($offset)
            ->take($this->itemsPerSitemap)
            ->get();
    }

    /**
     * دریافت ناشران صفحه مشخص
     */
    protected function getPublishersForPage($page)
    {
        $offset = ($page - 1) * $this->itemsPerSitemap;

        return DB::table('publishers')
            ->select('id', 'slug')
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.publisher_id = publishers.id AND posts.is_published = 1 AND posts.hide_content = 0) as posts_count'))
            ->orderBy('id')
            ->skip($offset)
            ->take($this->itemsPerSitemap)
            ->get();
    }

    /**
     * دریافت تگ‌های صفحه مشخص
     */
    protected function getTagsForPage($page)
    {
        $offset = ($page - 1) * $this->itemsPerSitemap;

        return DB::table('tags')
            ->select('id', 'slug')
            ->addSelect(DB::raw('(SELECT COUNT(*) FROM post_tag JOIN posts ON posts.id = post_tag.post_id WHERE post_tag.tag_id = tags.id AND posts.is_published = 1 AND posts.hide_content = 0) as posts_count'))
            ->orderBy('id')
            ->skip($offset)
            ->take($this->itemsPerSitemap)
            ->get();
    }

    /**
     * دریافت نقشه تصاویر برای پست‌ها
     */
    protected function getPostImagesMap($postIds)
    {
        if (empty($postIds)) {
            return [];
        }

        $result = [];

        // استفاده از یک کوئری بهینه برای گرفتن همه تصاویر
        $images = DB::table('post_images')
            ->select('post_id', 'image_path')
            ->whereIn('post_id', $postIds)
            ->where(function($query) {
                $query->whereNull('hide_image')
                    ->orWhere('hide_image', '!=', 'hidden');
            })
            ->orderBy('post_id')
            ->orderBy('sort_order')
            ->get();

        foreach ($images as $image) {
            if (!isset($result[$image->post_id])) {
                $result[$image->post_id] = [
                    'url' => $this->getImageUrl($image->image_path)
                ];
            }
        }

        return $result;
    }

    /**
     * تبدیل مسیر تصویر به URL کامل
     */
    protected function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return asset('images/default-book.png');
        }

        // URL مستقیم
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
        return asset('storage/' . $imagePath);
    }

    /**
     * محاسبه اولویت پست
     */
    protected function calculatePostPriority($post)
    {
        // پست‌های جدیدتر اولویت بالاتری دارند
        $baseValue = 0.6;

        // اگر سال انتشار دارد، در محاسبه در نظر بگیرید
        if ($post->publication_year) {
            $currentYear = date('Y');
            $yearDiff = $currentYear - $post->publication_year;

            // پست‌های جدیدتر اولویت بالاتری دارند
            if ($yearDiff <= 1) {
                $baseValue += 0.3; // کتاب‌های امسال یا سال قبل
            } elseif ($yearDiff <= 3) {
                $baseValue += 0.2; // کتاب‌های 2-3 سال اخیر
            } elseif ($yearDiff <= 5) {
                $baseValue += 0.1; // کتاب‌های 4-5 سال اخیر
            }
        }

        // تنظیم محدوده
        return min(0.9, max(0.4, $baseValue));
    }

    /**
     * محاسبه اولویت دسته‌بندی
     */
    protected function calculateCategoryPriority($category)
    {
        // دسته‌بندی‌های با پست بیشتر اهمیت بالاتری دارند
        $postsCount = $category->posts_count ?? 0;

        if ($postsCount > 100) {
            return 0.9;
        } elseif ($postsCount > 50) {
            return 0.8;
        } elseif ($postsCount > 20) {
            return 0.7;
        } elseif ($postsCount > 10) {
            return 0.6;
        } elseif ($postsCount > 0) {
            return 0.5;
        }

        return 0.4;
    }

    /**
     * محاسبه اولویت نویسنده
     */
    protected function calculateAuthorPriority($author)
    {
        // نویسندگان با پست بیشتر اهمیت بالاتری دارند
        $postsCount = ($author->posts_count ?? 0) + ($author->coauthored_count ?? 0);

        if ($postsCount > 50) {
            return 0.9;
        } elseif ($postsCount > 20) {
            return 0.8;
        } elseif ($postsCount > 10) {
            return 0.7;
        } elseif ($postsCount > 5) {
            return 0.6;
        } elseif ($postsCount > 0) {
            return 0.5;
        }

        return 0.4;
    }

    /**
     * محاسبه اولویت ناشر
     */
    protected function calculatePublisherPriority($publisher)
    {
        // ناشران با پست بیشتر اهمیت بالاتری دارند
        $postsCount = $publisher->posts_count ?? 0;

        if ($postsCount > 50) {
            return 0.9;
        } elseif ($postsCount > 20) {
            return 0.8;
        } elseif ($postsCount > 10) {
            return 0.7;
        } elseif ($postsCount > 5) {
            return 0.6;
        } elseif ($postsCount > 0) {
            return 0.5;
        }

        return 0.4;
    }

    /**
     * محاسبه اولویت تگ
     */
    protected function calculateTagPriority($tag)
    {
        // تگ‌های با پست بیشتر اهمیت بالاتری دارند
        $postsCount = $tag->posts_count ?? 0;

        if ($postsCount > 30) {
            return 0.8;
        } elseif ($postsCount > 15) {
            return 0.7;
        } elseif ($postsCount > 5) {
            return 0.6;
        } elseif ($postsCount > 0) {
            return 0.5;
        }

        return 0.4;
    }
}
