<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ایجاد ۳۲۴ دسته‌بندی با اعداد ۱ تا ۳۲۴
        for ($i = 1; $i <= 324; $i++) {
            $name = 'دسته‌بندی ' . $i;
            $slug = 'category-' . $i;

            Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        $this->command->info('۳۲۴ دسته‌بندی با موفقیت ایجاد شدند یا به‌روزرسانی شدند.');
    }
}
