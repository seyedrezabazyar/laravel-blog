<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url as SitemapUrl;

class SitemapController extends Controller
{
    /**
     * گرفتن sitemap کامل
     */
    public function index()
    {
        $sitemapIndex = SitemapIndex::create();

        // لینک به بقیه sitemap ها
        $sitemapIndex->add(URL::to('sitemap-posts'));
        $sitemapIndex->add(URL::to('sitemap-categories'));
        $sitemapIndex->add(URL::to('sitemap-authors'));
        $sitemapIndex->add(URL::to('sitemap-publishers'));
        $sitemapIndex->add(URL::to('sitemap-tags'));

        // خروجی به صورت XML
        return $sitemapIndex->toResponse(request());
    }

    /**
     * sitemap برای پست‌ها
     */
    public function posts()
    {
        $sitemap = Sitemap::create();

        // فقط پست‌های منتشر شده و غیر مخفی
        $posts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->orderBy('updated_at', 'desc')
            ->get();

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

        return $sitemap->toResponse(request());
    }

    /**
     * sitemap برای دسته‌بندی‌ها
     */
    public function categories()
    {
        $sitemap = Sitemap::create();

        $categories = Category::all();

        foreach ($categories as $category) {
            // تعیین اولویت بر اساس تعداد پست‌های دسته‌بندی
            $priority = min(1.0, (0.5 + ($category->posts()->count() / 100)));

            $sitemap->add(
                SitemapUrl::create(route('blog.category', $category->slug))
                    ->setLastModificationDate(Carbon::now())
                    ->setChangeFrequency('weekly')
                    ->setPriority($priority)
            );
        }

        return $sitemap->toResponse(request());
    }

    /**
     * sitemap برای نویسندگان
     */
    public function authors()
    {
        $sitemap = Sitemap::create();

        $authors = Author::all();

        foreach ($authors as $author) {
            // تعیین اولویت بر اساس تعداد پست‌های نویسنده
            $totalPosts = $author->posts()->count() + $author->coAuthoredPosts()->count();
            $priority = min(1.0, (0.5 + ($totalPosts / 50)));

            $sitemap->add(
                SitemapUrl::create(route('blog.author', $author->slug))
                    ->setLastModificationDate(Carbon::now())
                    ->setChangeFrequency('weekly')
                    ->setPriority($priority)
            );
        }

        return $sitemap->toResponse(request());
    }

    /**
     * sitemap برای ناشران
     */
    public function publishers()
    {
        $sitemap = Sitemap::create();

        $publishers = Publisher::all();

        foreach ($publishers as $publisher) {
            // تعیین اولویت بر اساس تعداد پست‌های ناشر
            $priority = min(1.0, (0.5 + ($publisher->posts()->count() / 50)));

            $sitemap->add(
                SitemapUrl::create(route('blog.publisher', $publisher->slug))
                    ->setLastModificationDate(Carbon::now())
                    ->setChangeFrequency('weekly')
                    ->setPriority($priority)
            );
        }

        return $sitemap->toResponse(request());
    }

    /**
     * sitemap برای تگ‌ها
     */
    public function tags()
    {
        $sitemap = Sitemap::create();

        $tags = Tag::all();

        foreach ($tags as $tag) {
            // تعیین اولویت بر اساس تعداد پست‌های این تگ
            $priority = min(1.0, (0.5 + ($tag->posts()->count() / 50)));

            $sitemap->add(
                SitemapUrl::create(route('blog.tag', $tag->slug))
                    ->setLastModificationDate(Carbon::now())
                    ->setChangeFrequency('weekly')
                    ->setPriority($priority)
            );
        }

        return $sitemap->toResponse(request());
    }
}
