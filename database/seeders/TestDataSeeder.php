<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

        // ایجاد دسته‌بندی‌ها
        $categories = [
            ['name' => 'آموزشی', 'slug' => 'education'],
            ['name' => 'تکنولوژی', 'slug' => 'technology'],
            ['name' => 'سبک زندگی', 'slug' => 'lifestyle'],
            ['name' => 'ادبیات', 'slug' => 'literature'],
            ['name' => 'تاریخ', 'slug' => 'history'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }

        $this->command->info('دسته‌بندی‌ها با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');

        // ایجاد نویسندگان تستی
        $authors = [
            ['name' => 'علی محمدی', 'slug' => 'ali-mohammadi', 'biography' => 'نویسنده و مترجم کتاب‌های آموزشی'],
            ['name' => 'سارا احمدی', 'slug' => 'sara-ahmadi', 'biography' => 'نویسنده کتاب‌های تاریخی و ادبی'],
            ['name' => 'محمد حسینی', 'slug' => 'mohammad-hosseini', 'biography' => 'متخصص حوزه تکنولوژی و برنامه‌نویسی'],
            ['name' => 'زهرا کریمی', 'slug' => 'zahra-karimi', 'biography' => 'نویسنده و پژوهشگر حوزه سبک زندگی'],
        ];

        foreach ($authors as $authorData) {
            Author::firstOrCreate(
                ['slug' => $authorData['slug']],
                [
                    'name' => $authorData['name'],
                    'biography' => $authorData['biography'],
                ]
            );
        }

        $this->command->info('نویسندگان با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');

        // ایجاد ناشران تستی
        $publishers = [
            ['name' => 'انتشارات نگاه', 'slug' => 'negah', 'description' => 'ناشر کتاب‌های ادبی و تاریخی'],
            ['name' => 'انتشارات فنی ایران', 'slug' => 'fanni-iran', 'description' => 'ناشر کتاب‌های فنی و مهندسی'],
            ['name' => 'نشر چشمه', 'slug' => 'cheshmeh', 'description' => 'ناشر کتاب‌های ادبی و هنری'],
            ['name' => 'انتشارات سمت', 'slug' => 'samt', 'description' => 'ناشر کتاب‌های آموزشی و دانشگاهی'],
        ];

        foreach ($publishers as $publisherData) {
            Publisher::firstOrCreate(
                ['slug' => $publisherData['slug']],
                [
                    'name' => $publisherData['name'],
                    'description' => $publisherData['description'],
                ]
            );
        }

        $this->command->info('ناشران با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');

        // ایجاد پست‌های تستی
        $existingPostsCount = Post::count();

        if ($existingPostsCount < 10) {
            $postsToCreate = 10 - $existingPostsCount;
            $categories = Category::all();
            $authors = Author::all();
            $publishers = Publisher::all();

            $bookTitles = [
                'راهنمای برنامه‌نویسی وب',
                'اصول موفقیت در زندگی',
                'تاریخ ایران باستان',
                'آموزش زبان انگلیسی',
                'مبانی هوش مصنوعی',
                'صد سال تنهایی',
                'آموزش پایتون برای همه',
                'فلسفه علم',
                'روانشناسی رفتار انسان',
                'فرهنگ و هنر ایران زمین'
            ];

            for ($i = 1; $i <= $postsToCreate; $i++) {
                $index = $i - 1;
                // اگر تعداد عناوین کمتر از تعداد پست‌های مورد نیاز است، از عنوان شماره‌ای استفاده می‌کنیم
                $title = isset($bookTitles[$index]) ? $bookTitles[$index] : "پست تستی شماره {$i}";
                $slug = Str::slug($title);

                // برای تعیین تصادفی نویسنده، ناشر، و دسته‌بندی
                $category = $categories->random();
                $author = $authors->random();
                $publisher = $publishers->random();
                $coAuthors = $authors->random(rand(0, 2))->pluck('id')->toArray(); // انتخاب تصادفی 0 تا 2 نویسنده همکار

                // محتوای پست
                $content = "<p>این یک پست تستی است برای کتاب {$title}.</p>
                <p>این کتاب توسط {$author->name} نوشته شده و توسط {$publisher->name} منتشر شده است.</p>
                <p>لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد.</p>";

                // ایجاد پست
                $post = Post::create([
                    'title' => $title,
                    'slug' => $slug,
                    'english_title' => 'English Title for ' . $title,
                    'content' => $content,
                    'english_content' => '<p>This is a test post for the book "' . $title . '".</p>',
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'author_id' => $author->id,
                    'publisher_id' => $publisher->id,
                    'md5_hash' => md5($title . time()), // ایجاد md5_hash منحصر به فرد
                    'language' => $i % 2 == 0 ? 'فارسی' : 'انگلیسی',
                    'publication_year' => rand(2010, (int)date('Y')),
                    'format' => $i % 3 == 0 ? 'چاپی' : ($i % 3 == 1 ? 'PDF' : 'EPUB'),
                    'book_codes' => 'ISBN: 978-3-16-148410-' . $i,
                    'keywords' => 'کلیدواژه۱, کلیدواژه۲, کلیدواژه۳',
                    'purchase_link' => 'https://example.com/books/' . $slug,
                    'is_published' => ($i <= 8), // 8 پست منتشر شده و 2 پیش‌نویس
                    'hide_image' => false,
                    'hide_content' => false,
                ]);

                // اضافه کردن نویسندگان همکار
                if (!empty($coAuthors)) {
                    $post->authors()->attach($coAuthors);
                }
            }

            $this->command->info("{$postsToCreate} پست تستی با موفقیت ایجاد شد.");
        } else {
            $this->command->info("پست‌های کافی در پایگاه داده وجود دارد. نیازی به ایجاد پست جدید نیست.");
        }
    }
}
