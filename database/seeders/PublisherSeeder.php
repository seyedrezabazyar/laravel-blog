<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\ImageUrlGenerator;

class PublisherSeeder extends Seeder
{
    use ImageUrlGenerator;

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
}
