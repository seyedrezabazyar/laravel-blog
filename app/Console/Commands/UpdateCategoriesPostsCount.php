<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateCategoriesPostsCount extends Command
{
    /**
     * نام و مشخصات دستور
     *
     * @var string
     */
    protected $signature = 'categories:update-counts';

    /**
     * توضیح دستور
     *
     * @var string
     */
    protected $description = 'تعداد پست‌های هر دسته‌بندی را به‌روزرسانی می‌کند';

    /**
     * اجرای دستور
     */
    public function handle()
    {
        $this->info('در حال به‌روزرسانی شمارنده پست‌ها برای هر دسته‌بندی...');

        // غیرفعال کردن موقت ایجاد رویداد برای جلوگیری از رویدادهای اضافی
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // گرفتن همه دسته‌بندی‌ها
        $categories = Category::all();

        $bar = $this->output->createProgressBar(count($categories));
        $bar->start();

        foreach ($categories as $category) {
            // شمارش مستقیم پست‌های مرتبط که منتشر شده و پنهان نیستند
            $postsCount = Post::where('category_id', $category->id)
                ->where('is_published', true)
                ->where('hide_content', false)
                ->count();

            // به‌روزرسانی مستقیم بدون تغییر دادن updated_at
            DB::table('categories')
                ->where('id', $category->id)
                ->update(['posts_count' => $postsCount]);

            $bar->advance();
        }

        $bar->finish();

        // فعال‌سازی مجدد بررسی کلید خارجی
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->info('شمارنده پست‌ها با موفقیت به‌روزرسانی شد!');
    }
}
