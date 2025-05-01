<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
                    'photo' => null,
                ]
            );

            if ($author->photo === null) {
                $author->update(['photo' => $this->getRandomImageUrl($author->id)]);
            }
        }

        $this->command->info('نویسندگان با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');
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
