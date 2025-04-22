<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ایجاد کاربر تستی و تعیین آن به عنوان مدیر
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'کاربر تستی',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin', // تعیین نقش مدیر
            ]
        );

        // اگر کاربر قبلاً وجود داشت، نقش آن را به مدیر تغییر دهید
        if ($user->wasRecentlyCreated == false) {
            $user->update(['role' => 'admin']);
        }

        $this->command->info('کاربر تستی با موفقیت به عنوان مدیر ایجاد یا به‌روزرسانی شد.');
        $this->command->info('ایمیل: test@example.com');
        $this->command->info('رمز عبور: password');

        // ایجاد سه دسته‌بندی (فقط اگر وجود نداشته باشند)
        $categories = [
            ['name' => 'آموزشی', 'slug' => 'education'],
            ['name' => 'تکنولوژی', 'slug' => 'technology'],
            ['name' => 'سبک زندگی', 'slug' => 'lifestyle'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }

        $this->command->info('دسته‌بندی‌ها با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');

        // ایجاد ده پست تستی
        $existingPostsCount = Post::count();

        if ($existingPostsCount < 10) {
            $postsToCreate = 10 - $existingPostsCount;

            for ($i = 1; $i <= $postsToCreate; $i++) {
                $title = "پست تستی شماره {$i}";
                Post::create([
                    'title' => $title,
                    'slug' => \Illuminate\Support\Str::slug($title),
                    'content' => "<p>این یک پست تستی است.</p>\n\n<p>این پست برای نمایش امکانات وبلاگ ایجاد شده است.</p>\n\n<p>لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد.</p>",
                    'user_id' => $user->id,
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'is_published' => ($i <= 8), // 8 پست منتشر شده و 2 پیش‌نویس
                ]);
            }

            $this->command->info("{$postsToCreate} پست تستی با موفقیت ایجاد شد.");
        } else {
            $this->command->info("پست‌های کافی در پایگاه داده وجود دارد. نیازی به ایجاد پست جدید نیست.");
        }
    }
}
