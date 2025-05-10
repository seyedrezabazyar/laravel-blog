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

class SitemapController extends Controller
{
    /**
     * مدت زمان کش به ثانیه (۱۴ روز)
     */
    protected $cacheTtl = 1209600;

    /**
     * تعداد آیتم‌ها در هر سایت‌مپ
     */
    protected $itemsPerSitemap = 50000;

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
            // محاسبه تعداد نقشه سایت‌های مورد نیاز
            $postSitemapCount = $this->getSitemapCount('posts');
            $categorySitemapCount = $this->getSitemapCount('categories');
            $authorSitemapCount = $this->getSitemapCount('authors');
            $publisherSitemapCount = $this->getSitemapCount('publishers');
            $tagSitemapCount = $this->getSitemapCount('tags');
            $imageSitemapCount = $this->getSitemapCount('images');

            // ایجاد شاخص سایت‌مپ
            $sitemapIndex = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $sitemapIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

            // اضافه کردن سایت‌مپ صفحه اصلی
            $sitemapIndex .= $this->getSitemapIndexEntry('sitemap-home.xml');

            // سایت‌مپ صفحات استاتیک
            $sitemapIndex .= $this->getSitemapIndexEntry('sitemap-static.xml');

            // اضافه کردن سایت‌مپ‌های انواع محتوا
            $sitemapIndex .= $this->addSitemapTypeEntries('posts', $postSitemapCount);
            $sitemapIndex .= $this->addSitemapTypeEntries('categories', $categorySitemapCount);
            $sitemapIndex .= $this->addSitemapTypeEntries('authors', $authorSitemapCount);
            $sitemapIndex .= $this->addSitemapTypeEntries('publishers', $publisherSitemapCount);
            $sitemapIndex .= $this->addSitemapTypeEntries('tags', $tagSitemapCount);

            // سایت‌مپ‌های تصاویر
            $sitemapIndex .= $this->addSitemapTypeEntries('images', $imageSitemapCount);

            $sitemapIndex .= '</sitemapindex>';

            return $this->createXmlResponse($sitemapIndex);
        });
    }

    /**
     * محاسبه تعداد نقشه سایت برای هر نوع محتوا
     */
    protected function getSitemapCount($type)
    {
        return Cache::remember("sitemap_{$type}_count", $this->cacheTtl, function () use ($type) {
            switch ($type) {
                case 'posts':
                    $count = Post::where('is_published', true)
                        ->where('hide_content', false)
                        ->count();
                    break;
                case 'categories':
                    $count = Category::count();
                    break;
                case 'authors':
                    $count = Author::count();
                    break;
                case 'publishers':
                    $count = Publisher::count();
                    break;
                case 'tags':
                    $count = Tag::count();
                    break;
                case 'images':
                    $count = DB::table('post_images')
                        ->join('posts', 'posts.id', '=', 'post_images.post_id')
                        ->where('posts.is_published', true)
                        ->where('posts.hide_content', false)
                        ->where(function($query) {
                            $query->whereNull('post_images.hide_image')
                                ->orWhere('post_images.hide_image', '!=', 'hidden');
                        })
                        ->count();
                    return max(1, ceil($count / 10000)); // تعداد کمتر برای تصاویر
                default:
                    $count = 0;
            }

            return max(1, ceil($count / $this->itemsPerSitemap));
        });
    }

    /**
     * افزودن بخش‌های نقشه سایت برای یک نوع محتوا
     */
    protected function addSitemapTypeEntries($type, $count)
    {
        $entries = '';
        for ($i = 1; $i <= $count; $i++) {
            $entries .= $this->getSitemapIndexEntry("sitemap-{$type}-{$i}.xml");
        }
        return $entries;
    }

    /**
     * ایجاد یک آیتم برای فایل شاخص سایت‌مپ
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
            $xml = $this->getUrlsetHeader();

            // صفحه اصلی
            $xml .= $this->getUrlXml(URL::to('/'), $this->lastmod, 'daily', '1.0');

            $xml .= '</urlset>';

            return $this->createXmlResponse($xml);
        });
    }

    /**
     * نمایش سایت‌مپ صفحات استاتیک
     */
    public function static()
    {
        return Cache::remember('sitemap_static', $this->cacheTtl, function () {
            $xml = $this->getUrlsetHeader();

            // صفحات استاتیک مهم
            $xml .= $this->getUrlXml(route('blog.categories'), $this->lastmod, 'weekly', '0.8');
            $xml .= $this->getUrlXml(route('blog.authors'), $this->lastmod, 'weekly', '0.8');
            $xml .= $this->getUrlXml(route('blog.publishers'), $this->lastmod, 'weekly', '0.8');
            $xml .= $this->getUrlXml(route('blog.tags'), $this->lastmod, 'weekly', '0.8');
            $xml .= $this->getUrlXml(route('blog.search'), $this->lastmod, 'weekly', '0.7');

            $xml .= '</urlset>';

            return $this->createXmlResponse($xml);
        });
    }

    /**
     * نمایش سایت‌مپ پست‌ها
     */
    public function posts($page = 1)
    {
        $cacheKey = "sitemap_posts_page_{$page}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($page) {
            // دریافت پست‌های این صفحه
            $posts = $this->getPostsForPage($page);

            if ($posts->isEmpty()) {
                abort(404);
            }

            // شروع XML
            $xml = $this->getUrlsetHeader();

            // اضافه کردن URL برای هر پست
            foreach ($posts as $post) {
                $lastmod = $post->updated_at ? $post->updated_at->toAtomString() : $this->lastmod;
                $url = route('blog.show', $post->slug);
                $priority = $this->calculatePostPriority($post);

                $xml .= $this->getUrlXml($url, $lastmod, $this->defaultChangefreq, $priority);
            }

            $xml .= '</urlset>';

            return $this->createXmlResponse($xml);
        });
    }

    /**
     * نمایش سایت‌مپ تصاویر
     */
    public function images($page = 1)
    {
        $cacheKey = "sitemap_images_page_{$page}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($page) {
            // دریافت تصاویر این صفحه
            $imagesPerSitemap = 10000;
            $offset = ($page - 1) * $imagesPerSitemap;

            $images = DB::table('post_images')
                ->join('posts', 'posts.id', '=', 'post_images.post_id')
                ->select([
                    'post_images.post_id',
                    'post_images.image_path',
                    'post_images.caption',
                    'posts.slug as post_slug',
                    'posts.title'
                ])
                ->where('posts.is_published', true)
                ->where('posts.hide_content', false)
                ->where(function($query) {
                    $query->whereNull('post_images.hide_image')
                        ->orWhere('post_images.hide_image', '!=', 'hidden');
                })
                ->orderBy('post_images.post_id')
                ->skip($offset)
                ->take($imagesPerSitemap)
                ->get();

            if ($images->isEmpty()) {
                abort(404);
            }

            // شروع XML با namespace تصویر
            $xml = $this->getUrlsetHeader(true);

            // گروه‌بندی تصاویر بر اساس پست
            $postImages = [];
            foreach ($images as $image) {
                if (!isset($postImages[$image->post_id])) {
                    $postImages[$image->post_id] = [
                        'slug' => $image->post_slug,
                        'title' => $image->title,
                        'images' => []
                    ];
                }

                $postImages[$image->post_id]['images'][] = [
                    'url' => $this->getImageUrl($image->image_path),
                    'caption' => $image->caption ?? $image->title
                ];
            }

            // ایجاد بخش‌های XML برای هر پست و تصاویر آن
            foreach ($postImages as $postId => $data) {
                $postUrl = route('blog.show', $data['slug']);

                $xml .= "\t<url>\n";
                $xml .= "\t\t<loc>{$postUrl}</loc>\n";
                $xml .= "\t\t<lastmod>{$this->lastmod}</lastmod>\n";

                foreach ($data['images'] as $image) {
                    $imageUrl = htmlspecialchars($image['url'], ENT_XML1);
                    $caption = htmlspecialchars($image['caption'], ENT_XML1);

                    $xml .= "\t\t<image:image>\n";
                    $xml .= "\t\t\t<image:loc>{$imageUrl}</image:loc>\n";
                    $xml .= "\t\t\t<image:title>{$caption}</image:title>\n";
                    $xml .= "\t\t</image:image>\n";
                }

                $xml .= "\t</url>\n";
            }

            $xml .= '</urlset>';

            return $this->createXmlResponse($xml);
        });
    }

    /**
     * نمایش سایت‌مپ دسته‌بندی‌ها
     */
    public function categories($page = 1)
    {
        // مشابه متد posts، بر اساس دسته‌بندی‌ها
    }

    /**
     * نمایش سایت‌مپ نویسندگان
     */
    public function authors($page = 1)
    {
        // مشابه متد posts، بر اساس نویسندگان
    }

    /**
     * نمایش سایت‌مپ ناشران
     */
    public function publishers($page = 1)
    {
        // مشابه متد posts، بر اساس ناشران
    }

    /**
     * نمایش سایت‌مپ تگ‌ها
     */
    public function tags($page = 1)
    {
        // مشابه متد posts، بر اساس تگ‌ها
    }

    /**
     * دریافت هدر XML با namespace اختیاری تصویر
     */
    protected function getUrlsetHeader($withImageNamespace = false)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';

        if ($withImageNamespace) {
            $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ';
        }

        $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . PHP_EOL;

        return $xml;
    }

    /**
     * دریافت بخش URL در XML
     */
    protected function getUrlXml($loc, $lastmod, $changefreq, $priority)
    {
        return "\t<url>\n\t\t<loc>{$loc}</loc>\n\t\t<lastmod>{$lastmod}</lastmod>\n\t\t<changefreq>{$changefreq}</changefreq>\n\t\t<priority>{$priority}</priority>\n\t</url>\n";
    }

    /**
     * ایجاد پاسخ XML با هدرهای مناسب
     */
    protected function createXmlResponse($content)
    {
        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl);
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
     * تبدیل مسیر تصویر به URL کامل
     */
    protected function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return asset('images/default-book.png');
        }

        // URL مستقیم
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }

        // برای دامنه images.balyan.ir
        if (str_contains($imagePath, 'images.balyan.ir/')) {
            return 'https://' . $imagePath;
        }

        // برای تصاویر هاست دانلود
        if (str_starts_with($imagePath, 'post_images/') || str_starts_with($imagePath, 'posts/')) {
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

    // سایر متدهای لازم برای محاسبه اولویت‌ها...
}
