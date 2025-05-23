<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use App\Models\Author;
use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();
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
                $title = isset($bookTitles[$index]) ? $bookTitles[$index] : "پست تستی شماره {$i}";
                $baseSlug = Str::slug($title);
                $slug = $baseSlug . '-' . Str::random(6);

                $category = $categories->random();
                $author = $authors->random();
                $publisher = $publishers->random();
                $coAuthors = $authors->random(rand(0, 2))->pluck('id')->toArray();

                $content = "<p>این یک پست تستی است برای کتاب {$title}.</p>
                <p>این کتاب توسط {$author->name} نوشته شده و توسط {$publisher->name} منتشر شده است.</p>
                <p>لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است.</p>";

                try {
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
                        'is_published' => ($i <= 8),
                        'hide_content' => false,
                    ]);

                    if (!empty($coAuthors)) {
                        $post->authors()->attach($coAuthors);
                    }

                    // تغییر مقدار hide_image از false/0 به 'visible'
                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => $this->getRandomImageUrl($post->id),
                        'caption' => 'تصویر اصلی برای کتاب ' . $title,
                        'hide_image' => 'visible', // تغییر به 'visible' به جای false یا 0
                        'sort_order' => 0
                    ]);

                } catch (\Exception $e) {
                    $this->command->error("خطا در ایجاد پست {$title}: " . $e->getMessage());
                }
            }

            $this->command->info("{$postsToCreate} پست تستی با موفقیت ایجاد شد.");
        } else {
            $posts = Post::all();
            // $tagCollection = collect(Tag::all()); // حذف شده

            foreach ($posts as $post) {

                if ($post->images()->count() == 0) {
                    // تغییر مقدار hide_image از false/0 به 'visible'
                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => $this->getRandomImageUrl($post->id),
                        'caption' => 'تصویر اصلی برای کتاب ' . $post->title,
                        'hide_image' => 'visible', // تغییر به 'visible' به جای false یا 0
                        'sort_order' => 0
                    ]);
                }
            }

            $this->command->info("تصاویر با موفقیت به پست‌های موجود اضافه شدند.");
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

        $hash = md5(uniqid(rand(), true));

        if ($postId === null) {
            $postId = rand(1, 40000);
        }

        $folderBase = floor(($postId - 1) / 10000) * 10000;
        $folder = $folderBase === 0 ? "0" : (string)$folderBase;

        return "https://images.balyan.ir/{$folder}/{$hash}.{$format}";
    }
}
