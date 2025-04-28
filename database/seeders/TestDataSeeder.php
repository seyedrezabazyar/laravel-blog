<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update test user as admin
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'کاربر تستی',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'admin',
            ]
        );

        // If user already existed, update role to admin
        if ($user->wasRecentlyCreated == false) {
            $user->update(['role' => 'admin']);
        }

        $this->command->info('کاربر تستی با موفقیت به عنوان مدیر ایجاد یا به‌روزرسانی شد.');
        $this->command->info('ایمیل: test@example.com');
        $this->command->info('رمز عبور: password');

        // Create categories
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

        // Create test authors
        $authors = [
            ['name' => 'علی محمدی', 'slug' => 'ali-mohammadi', 'biography' => 'نویسنده و مترجم کتاب‌های آموزشی'],
            ['name' => 'سارا احمدی', 'slug' => 'sara-ahmadi', 'biography' => 'نویسنده کتاب‌های تاریخی و ادبی'],
            ['name' => 'محمد حسینی', 'slug' => 'mohammad-hosseini', 'biography' => 'متخصص حوزه تکنولوژی و برنامه‌نویسی'],
            ['name' => 'زهرا کریمی', 'slug' => 'zahra-karimi', 'biography' => 'نویسنده و پژوهشگر حوزه سبک زندگی'],
        ];

        foreach ($authors as $authorData) {
            $author = Author::firstOrCreate(
                ['slug' => $authorData['slug']],
                [
                    'name' => $authorData['name'],
                    'biography' => $authorData['biography'],
                    'photo' => null, // Will be updated after creation to use correct ID
                ]
            );

            // Update photo with correct folder structure based on ID
            if ($author->photo === null) {
                $author->update(['photo' => $this->getRandomImageUrl($author->id)]);
            }
        }

        $this->command->info('نویسندگان با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');

        // Create test publishers
        $publishers = [
            ['name' => 'انتشارات نگاه', 'slug' => 'negah', 'description' => 'ناشر کتاب‌های ادبی و تاریخی'],
            ['name' => 'انتشارات فنی ایران', 'slug' => 'fanni-iran', 'description' => 'ناشر کتاب‌های فنی و مهندسی'],
            ['name' => 'نشر چشمه', 'slug' => 'cheshmeh', 'description' => 'ناشر کتاب‌های ادبی و هنری'],
            ['name' => 'انتشارات سمت', 'slug' => 'samt', 'description' => 'ناشر کتاب‌های آموزشی و دانشگاهی'],
        ];

        foreach ($publishers as $publisherData) {
            $publisher = Publisher::firstOrCreate(
                ['slug' => $publisherData['slug']],
                [
                    'name' => $publisherData['name'],
                    'description' => $publisherData['description'],
                    'logo' => null, // Will be updated after creation to use correct ID
                ]
            );

            // Update logo with correct folder structure based on ID
            if ($publisher->logo === null) {
                $publisher->update(['logo' => $this->getRandomImageUrl($publisher->id)]);
            }
        }

        $this->command->info('ناشران با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');

        // Create test tags
        $tags = [
            ['name' => 'آموزش', 'slug' => 'education'],
            ['name' => 'برنامه‌نویسی', 'slug' => 'programming'],
            ['name' => 'توسعه فردی', 'slug' => 'personal-development'],
            ['name' => 'تاریخی', 'slug' => 'historical'],
            ['name' => 'هوش مصنوعی', 'slug' => 'artificial-intelligence'],
            ['name' => 'روانشناسی', 'slug' => 'psychology'],
            ['name' => 'ادبیات', 'slug' => 'literature'],
            ['name' => 'علمی', 'slug' => 'scientific'],
            ['name' => 'فلسفه', 'slug' => 'philosophy'],
            ['name' => 'هنر', 'slug' => 'art'],
        ];

        $tagModels = [];
        foreach ($tags as $tagData) {
            $tag = Tag::firstOrCreate(
                ['slug' => $tagData['slug']],
                ['name' => $tagData['name']]
            );
            $tagModels[] = $tag;
        }

        $this->command->info('تگ‌ها با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');

        // Create test posts
        $existingPostsCount = Post::count();

        if ($existingPostsCount < 10) {
            $postsToCreate = 10 - $existingPostsCount;
            $categories = Category::all();
            $authors = Author::all();
            $publishers = Publisher::all();
            $tagCollection = collect($tagModels);

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
                $baseSlug = Str::slug($title);

                // ایجاد یک اسلاگ منحصر به فرد با افزودن یک شناسه تصادفی
                $slug = $baseSlug . '-' . Str::random(6);

                // Randomly select category, author, and publisher
                $category = $categories->random();
                $author = $authors->random();
                $publisher = $publishers->random();
                $coAuthors = $authors->random(rand(0, 2))->pluck('id')->toArray();

                // Post content
                $content = "<p>این یک پست تستی است برای کتاب {$title}.</p>
                <p>این کتاب توسط {$author->name} نوشته شده و توسط {$publisher->name} منتشر شده است.</p>
                <p>لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد.</p>";

                try {
                    // Create post with basic fields first
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
                        'md5_hash' => md5($title . time() . uniqid(rand(), true)),
                        'language' => $i % 2 == 0 ? 'فارسی' : 'انگلیسی',
                        'publication_year' => rand(2010, (int)date('Y')),
                        'format' => $i % 3 == 0 ? 'چاپی' : ($i % 3 == 1 ? 'PDF' : 'EPUB'),
                        'book_codes' => 'ISBN: 978-3-16-148410-' . $i,
                        'purchase_link' => 'https://example.com/books/' . $slug,
                        'is_published' => ($i <= 8), // 8 published posts and 2 drafts
                        'hide_content' => false,
                    ]);

                    // Add co-authors
                    if (!empty($coAuthors)) {
                        $post->authors()->attach($coAuthors);
                    }

                    // Add random tags to post
                    $randomTagCount = rand(2, 5);
                    $randomTags = $tagCollection->random($randomTagCount);
                    $post->tags()->attach($randomTags->pluck('id')->toArray());

                    // در اینجا فقط یک تصویر اصلی برای پست اضافه می‌کنیم
                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => $this->getRandomImageUrl($post->id),
                        'caption' => 'تصویر اصلی برای کتاب ' . $title,
                        'hide_image' => false,
                        'sort_order' => 0 // تصویر اصلی
                    ]);

                } catch (\Exception $e) {
                    $this->command->error("خطا در ایجاد پست {$title}: " . $e->getMessage());
                }
            }

            $this->command->info("{$postsToCreate} پست تستی با موفقیت ایجاد شد.");
        } else {
            $this->command->info("پست‌های کافی در پایگاه داده وجود دارد. نیازی به ایجاد پست جدید نیست.");

            // Update existing posts with image URLs if they don't have them
            $posts = Post::all();
            $tagCollection = collect($tagModels);

            foreach ($posts as $post) {
                // Add tags if they don't exist
                if ($post->tags()->count() == 0) {
                    $randomTagCount = rand(2, 5);
                    $randomTags = $tagCollection->random($randomTagCount);
                    $post->tags()->attach($randomTags->pluck('id')->toArray());
                }

                // Add post image if it doesn't exist
                if ($post->images()->count() == 0) {
                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => $this->getRandomImageUrl($post->id),
                        'caption' => 'تصویر اصلی برای کتاب ' . $post->title,
                        'hide_image' => false,
                        'sort_order' => 0
                    ]);
                }
            }

            $this->command->info("تصاویر و تگ‌ها با موفقیت به پست‌های موجود اضافه شدند.");
        }

        // Update authors' photos if they don't have them
        $authors = Author::whereNull('photo')->get();
        foreach ($authors as $author) {
            // Use author ID as post ID for folder structure
            $author->update(['photo' => $this->getRandomImageUrl($author->id)]);
        }

        // Update publishers' logos if they don't have them
        $publishers = Publisher::whereNull('logo')->get();
        foreach ($publishers as $publisher) {
            // Use publisher ID as post ID for folder structure
            $publisher->update(['logo' => $this->getRandomImageUrl($publisher->id)]);
        }
    }

    /**
     * Generate an image URL with the specified folder structure based on post ID
     *
     * @param int|null $postId Post ID to determine the folder
     * @return string
     */
    private function getRandomImageUrl(?int $postId = null): string
    {
        $imageFormats = ['jpg', 'png', 'webp'];
        $format = $imageFormats[array_rand($imageFormats)];

        // Generate a random hash for the image name
        $hash = md5(uniqid(rand(), true));

        // Determine folder based on post ID range
        // If post ID is null or not provided, use a random post ID simulation
        if ($postId === null) {
            $postId = rand(1, 40000); // Simulate a random post ID
        }

        // Calculate folder name based on the rule:
        // Posts 1-10000 -> folder "0"
        // Posts 10001-20000 -> folder "10000"
        // Posts 20001-30000 -> folder "20000"
        // etc.
        $folderBase = floor(($postId - 1) / 10000) * 10000;
        $folder = $folderBase === 0 ? "0" : (string)$folderBase;

        return "https://images.balyan.ir/{$folder}/{$hash}.{$format}";
    }
}
