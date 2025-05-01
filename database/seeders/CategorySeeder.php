<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
/**
* Run the database seeds.
*/
public function run(): void
{
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
}
}
