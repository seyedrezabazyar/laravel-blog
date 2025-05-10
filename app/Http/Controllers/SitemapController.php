<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{
    /**
     * نمایش سایت‌مپ اصلی
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->serveSitemap('sitemap.php');
    }

    /**
     * نمایش سایت‌مپ صفحات استاتیک
     *
     * @return \Illuminate\Http\Response
     */
    public function pages()
    {
        return $this->serveSitemap('sitemap-pages.xml');
    }

    /**
     * نمایش ایندکس سایت‌مپ پست‌ها
     *
     * @return \Illuminate\Http\Response
     */
    public function posts()
    {
        return $this->serveSitemap('sitemap-posts.xml');
    }

    /**
     * نمایش سایت‌مپ پست‌های یک صفحه خاص
     *
     * @param int $page شماره صفحه
     * @return \Illuminate\Http\Response
     */
    public function postsPage($page)
    {
        return $this->serveSitemap("sitemap-posts-{$page}.xml");
    }

    /**
     * نمایش ایندکس سایت‌مپ تصاویر پست‌ها
     *
     * @return \Illuminate\Http\Response
     */
    public function postImages()
    {
        return $this->serveSitemap('sitemap-post-images.xml');
    }

    /**
     * نمایش سایت‌مپ تصاویر پست‌های یک صفحه خاص
     *
     * @param int $page شماره صفحه
     * @return \Illuminate\Http\Response
     */
    public function postImagesPage($page)
    {
        return $this->serveSitemap("sitemap-post-images-{$page}.xml");
    }

    /**
     * نمایش ایندکس سایت‌مپ دسته‌بندی‌ها
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        return $this->serveSitemap('sitemap-categories.xml');
    }

    /**
     * نمایش سایت‌مپ دسته‌بندی‌های یک صفحه خاص
     *
     * @param int $page شماره صفحه
     * @return \Illuminate\Http\Response
     */
    public function categoriesPage($page)
    {
        return $this->serveSitemap("sitemap-categories-{$page}.xml");
    }

    /**
     * نمایش ایندکس سایت‌مپ نویسندگان
     *
     * @return \Illuminate\Http\Response
     */
    public function authors()
    {
        return $this->serveSitemap('sitemap-authors.xml');
    }

    /**
     * نمایش سایت‌مپ نویسندگان یک صفحه خاص
     *
     * @param int $page شماره صفحه
     * @return \Illuminate\Http\Response
     */
    public function authorsPage($page)
    {
        return $this->serveSitemap("sitemap-authors-{$page}.xml");
    }

    /**
     * نمایش ایندکس سایت‌مپ ناشران
     *
     * @return \Illuminate\Http\Response
     */
    public function publishers()
    {
        return $this->serveSitemap('sitemap-publishers.xml');
    }

    /**
     * نمایش سایت‌مپ ناشران یک صفحه خاص
     *
     * @param int $page شماره صفحه
     * @return \Illuminate\Http\Response
     */
    public function publishersPage($page)
    {
        return $this->serveSitemap("sitemap-publishers-{$page}.xml");
    }

    /**
     * نمایش ایندکس سایت‌مپ برچسب‌ها
     *
     * @return \Illuminate\Http\Response
     */
    public function tags()
    {
        return $this->serveSitemap('sitemap-tags.xml');
    }

    /**
     * نمایش سایت‌مپ برچسب‌های یک صفحه خاص
     *
     * @param int $page شماره صفحه
     * @return \Illuminate\Http\Response
     */
    public function tagsPage($page)
    {
        return $this->serveSitemap("sitemap-tags-{$page}.xml");
    }

    /**
     * ارائه فایل سایت‌مپ
     *
     * @param string $filename نام فایل
     * @return \Illuminate\Http\Response
     */
    protected function serveSitemap($filename)
    {
        // بررسی وجود فایل در مسیر عمومی
        if ($filename === 'sitemap.php' && Storage::exists('public/' . $filename)) {
            $filePath = Storage::path('public/' . $filename);
        } else {
            // چک کردن در مسیر ذخیره‌سازی اصلی سایت‌مپ
            $storagePath = 'public/sitemaps/' . $filename;

            if (!Storage::exists($storagePath)) {
                abort(404, 'سایت‌مپ یافت نشد');
            }

            $filePath = Storage::path($storagePath);
        }

        $lastModified = date('D, d M Y H:i:s', filemtime($filePath)) . ' GMT';

        return response()->file(
            $filePath, [
                'Content-Type' => 'application/xml; charset=utf-8',
                'Content-Length' => filesize($filePath),
                'Cache-Control' => 'public, max-age=86400',
                'Last-Modified' => $lastModified
            ]
        );
    }
}
