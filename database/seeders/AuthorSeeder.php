<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\ImageUrlGenerator;

class AuthorSeeder extends Seeder
{
    use ImageUrlGenerator;

    /**
     * Run the database seeds.
     */
    public function run%: void
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
}
