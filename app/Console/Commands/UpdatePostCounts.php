<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePostCounts extends Command
{
    /**
     * نام و مشخصات دستور
     */
    protected $signature = 'posts:update-counts {--type=all : نوع شمارنده (categories, authors, publishers, all)}';

    /**
     * توضیح دستور
     */
    protected $description = 'به‌روزرسانی شمارنده‌های پست‌ها برای دسته‌بندی‌ها، نویسندگان و ناشران';

    /**
     * اجرای دستور
     */
    public function handle()
    {
        $type = $this->option('type');

        $this->info('شروع به‌روزرسانی شمارنده‌ها...');

        // غیرفعال کردن موقت timestamp ها برای بهتر شدن عملکرد
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        if ($type === 'all' || $type === 'categories') {
            $this->updateCategoriesCount();
        }

        if ($type === 'all' || $type === 'authors') {
            $this->updateAuthorsCount();
        }

        if ($type === 'all' || $type === 'publishers') {
            $this->updatePublishersCount();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('به‌روزرسانی شمارنده‌ها با موفقیت تکمیل شد!');

        return 0;
    }

    /**
     * به‌روزرسانی شمارنده دسته‌بندی‌ها
     */
    private function updateCategoriesCount()
    {
        $this->info('به‌روزرسانی شمارنده دسته‌بندی‌ها...');

        $categories = Category::all();
        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        foreach ($categories as $category) {
            $postsCount = Post::where('category_id', $category->id)
                ->where('is_published', true)
                ->where('hide_content', false)
                ->count();

            // به‌روزرسانی مستقیم بدون تغییر updated_at
            DB::table('categories')
                ->where('id', $category->id)
                ->update(['posts_count' => $postsCount]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('شمارنده دسته‌بندی‌ها به‌روزرسانی شد.');
    }

    /**
     * به‌روزرسانی شمارنده نویسندگان
     */
    private function updateAuthorsCount()
    {
        $this->info('به‌روزرسانی شمارنده نویسندگان...');

        $authors = Author::all();
        $bar = $this->output->createProgressBar($authors->count());
        $bar->start();

        foreach ($authors as $author) {
            // شمارش پست‌های نویسنده اصلی
            $mainPostsCount = Post::where('author_id', $author->id)
                ->where('is_published', true)
                ->where('hide_content', false)
                ->count();

            // شمارش پست‌های همکاری
            $coAuthoredCount = DB::table('post_author')
                ->join('posts', 'posts.id', '=', 'post_author.post_id')
                ->where('post_author.author_id', $author->id)
                ->where('posts.is_published', true)
                ->where('posts.hide_content', false)
                ->count();

            $totalCount = $mainPostsCount + $coAuthoredCount;

            // به‌روزرسانی مستقیم بدون تغییر updated_at
            DB::table('authors')
                ->where('id', $author->id)
                ->update(['posts_count' => $totalCount]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('شمارنده نویسندگان به‌روزرسانی شد.');
    }

    /**
     * به‌روزرسانی شمارنده ناشران
     */
    private function updatePublishersCount()
    {
        $this->info('به‌روزرسانی شمارنده ناشران...');

        $publishers = Publisher::all();
        $bar = $this->output->createProgressBar($publishers->count());
        $bar->start();

        foreach ($publishers as $publisher) {
            $postsCount = Post::where('publisher_id', $publisher->id)
                ->where('is_published', true)
                ->where('hide_content', false)
                ->count();

            // به‌روزرسانی مستقیم بدون تغییر updated_at
            DB::table('publishers')
                ->where('id', $publisher->id)
                ->update(['posts_count' => $postsCount]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('شمارنده ناشران به‌روزرسانی شد.');
    }
}
