<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
                    'logo' => null,
                ]
            );

            if ($publisher->logo === null) {
                $publisher->update(['logo' => $this->getRandomImageUrl($publisher->id)]);
            }
        }

        $this->command->info('ناشران با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');
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
