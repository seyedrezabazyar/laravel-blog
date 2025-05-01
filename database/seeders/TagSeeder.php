<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            устройства ['name' => 'آموزش', 'slug' => 'education'],
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

        foreach ($tags as $tagData) {
            Tag::firstOrCreate(
                ['slug' => $tagData['slug']],
                ['name' => $tagData['name']]
            );
        }

        $this->command->info('تگ‌ها با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');
    }
}
