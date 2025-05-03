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
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url as SitemapUrl;

class SitemapController extends Controller
{
    /**
     * گرفتن sitemap کامل - کش شده
     */
    public function index()
    {
        return Cache::remember('sitemap_index', 86400, function () {
            $sitemapIndex = SitemapIndex::create();

            $sitemapIndex->add(URL::to('sitemap-posts'));
            $sitemapIndex->add(URL::to('sitemap-categories'));
            $sitemapIndex->add(URL::to('sitemap-authors'));
            $sitemapIndex->add(URL::to('sitemap-publishers'));
            $sitemapIndex->add(URL::to('sitemap-tags'));

            return $sitemapIndex->toResponse(request());
        });
    }

    /**
     * sitemap برای پست‌ها - کش شده و بخش‌بندی شده
     */
    public function posts()
    {
        return Cache::remember('sitemap_posts', 86400, function () {
            $sitemap = Sitemap::create();

            // بهینه‌سازی با کوئری chunk برای کاهش مصرف حافظه
            Post::where('is_published', true)
                ->where('hide_content', false)
                ->select('id', 'slug', 'title', 'updated_at')
                ->with(['featuredImage' => function($query) {
                    $query->select('id', 'post_id', 'image_path', 'hide_image');
                }])
                ->orderBy('updated_at', 'desc')
                ->chunk(1000, function ($posts) use ($sitemap) {
                    foreach ($posts as $post) {
                        $url = SitemapUrl::create(route('blog.show', $post->slug))
                            ->setLastModificationDate($post->updated_at)
                            ->setChangeFrequency('weekly')
                            ->setPriority(0.8);

                        // افزودن تصویر اصلی اگر وجود داشته باشد و مخفی نباشد
                        if ($post->featuredImage && !$post->featuredImage->hide_image) {
                            $url->addImage($post->featuredImage->display_url, $post->title);
                        }

                        $sitemap->add($url);
                    }
                });

            return $sitemap->toResponse(request());
        });
    }

    /**
     * sitemap برای دسته‌بندی‌ها - کش شده
     */
    public function categories()
    {
        return Cache::remember('sitemap_categories', 86400, function () {
            $sitemap = Sitemap::create();

            // انتخاب فقط فیلدهای مورد نیاز
            Category::select('id', 'slug')
                ->withCount('posts')
                ->get()
                ->each(function ($category) use ($sitemap) {
                    // تعیین اولویت بر اساس تعداد پست‌های دسته‌بندی
                    $priority = min(1.0, (0.5 + ($category->posts_count / 100)));

                    $sitemap->add(
                        SitemapUrl::create(route('blog.category', $category->slug))
                            ->setLastModificationDate(Carbon::now())
                            ->setChangeFrequency('weekly')
                            ->setPriority($priority)
                    );
                });

            return $sitemap->toResponse(request());
        });
    }

    /**
     * sitemap برای نویسندگان - کش شده
     */
    public function authors()
    {
        return Cache::remember('sitemap_authors', 86400, function () {
            $sitemap = Sitemap::create();

            $authors = Author::select('id', 'slug')
                ->withCount('posts')
                ->withCount('coAuthoredPosts as coauthored_count')
                ->get();

            foreach ($authors as $author) {
                // تعیین اولویت بر اساس تعداد پست‌های نویسنده
                $totalPosts = $author->posts_count + $author->coauthored_count;
                $priority = min(1.0, (0.5 + ($totalPosts / 50)));

                $sitemap->add(
                    SitemapUrl::create(route('blog.author', $author->slug))
                        ->setLastModificationDate(Carbon::now())
                        ->setChangeFrequency('weekly')
                        ->setPriority($priority)
                );
            }

            return $sitemap->toResponse(request());
        });
    }

    /**
     * sitemap برای ناشران - کش شده
     */
    public function publishers()
    {
        return Cache::remember('sitemap_publishers', 86400, function () {
            $sitemap = Sitemap::create();

            Publisher::select('id', 'slug')
                ->withCount('posts')
                ->get()
                ->each(function ($publisher) use ($sitemap) {
                    // تعیین اولویت بر اساس تعداد پست‌های ناشر
                    $priority = min(1.0, (0.5 + ($publisher->posts_count / 50)));

                    $sitemap->add(
                        SitemapUrl::create(route('blog.publisher', $publisher->slug))
                            ->setLastModificationDate(Carbon::now())
                            ->setChangeFrequency('weekly')
                            ->setPriority($priority)
                    );
                });

            return $sitemap->toResponse(request());
        });
    }

    /**
     * sitemap برای تگ‌ها - کش شده
     */
    public function tags()
    {
        return Cache::remember('sitemap_tags', 86400, function () {
            $sitemap = Sitemap::create();

            Tag::select('id', 'slug')
                ->withCount('posts')
                ->get()
                ->each(function ($tag) use ($sitemap) {
                    // تعیین اولویت بر اساس تعداد پست‌های این تگ
                    $priority = min(1.0, (0.5 + ($tag->posts_count / 50)));

                    $sitemap->add(
                        SitemapUrl::create(route('blog.tag', $tag->slug))
                            ->setLastModificationDate(Carbon::now())
                            ->setChangeFrequency('weekly')
                            ->setPriority($priority)
                    );
                });

            return $sitemap->toResponse(request());
        });
    }
}
