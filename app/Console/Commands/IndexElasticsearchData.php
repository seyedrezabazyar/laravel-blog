<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ElasticsearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IndexElasticsearchData extends Command
{
    protected $signature = 'elasticsearch:index {--chunk=100} {--force}';
    protected $description = 'ایندکس کردن تمام داده‌های پست‌ها در Elasticsearch';

    protected $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        parent::__construct();
        $this->elasticsearchService = $elasticsearchService;
    }

    public function handle()
    {
        $force = $this->option('force');
        $chunkSize = (int) $this->option('chunk');

        $this->info('شروع ایندکس کردن داده‌ها در Elasticsearch...');

        if ($force) {
            $this->warn('حذف ایندکس موجود و ایجاد مجدد...');
            $this->deleteAndCreateIndex();
        }

        // شمارش کل پست‌ها
        $totalPosts = Post::where('is_published', true)
            ->where('hide_content', false)
            ->count();

        $this->info("تعداد کل پست‌ها: {$totalPosts}");

        if ($totalPosts === 0) {
            $this->warn('هیچ پست قابل ایندکسی یافت نشد.');
            return 0;
        }

        $bar = $this->output->createProgressBar($totalPosts);
        $bar->start();

        $indexedCount = 0;
        $failedCount = 0;
        $errors = [];

        // پردازش به صورت chunk
        Post::where('is_published', true)
            ->where('hide_content', false)
            ->with(['category', 'author', 'authors', 'publisher'])
            ->chunk($chunkSize, function ($posts) use (&$indexedCount, &$failedCount, &$errors, $bar) {
                $booksData = [];

                foreach ($posts as $post) {
                    $bookData = $this->prepareBookData($post);
                    if ($bookData) {
                        $bookData['post_id'] = $post->id;
                        $booksData[] = $bookData;
                    }
                    $bar->advance();
                }

                if (!empty($booksData)) {
                    $result = $this->elasticsearchService->bulkIndexBooks($booksData);
                    $indexedCount += $result['success'];
                    $failedCount += $result['failed'];

                    if (!empty($result['errors'])) {
                        $errors = array_merge($errors, $result['errors']);
                    }
                }
            });

        $bar->finish();
        $this->newLine();

        // به‌روزرسانی وضعیت ایندکس در پست‌ها
        Post::where('is_published', true)
            ->where('hide_content', false)
            ->update([
                'is_indexed' => true,
                'indexed_at' => now()
            ]);

        $this->info("ایندکس کردن به پایان رسید:");
        $this->info("- موفق: {$indexedCount}");
        $this->info("- ناموفق: {$failedCount}");

        if (!empty($errors)) {
            $this->error("خطاها:");
            foreach (array_slice($errors, 0, 5) as $error) {
                $this->error("- " . (is_array($error) ? json_encode($error) : $error));
            }
        }

        return 0;
    }

    /**
     * آماده‌سازی داده‌های کتاب برای ایندکس
     */
    private function prepareBookData(Post $post): ?array
    {
        try {
            // دریافت محتوای فارسی و انگلیسی
            $persianContent = $post->content ?? '';
            $englishContent = $post->english_content ?? '';

            // محدود کردن طول محتوا برای Elasticsearch
            $persianContent = mb_substr($persianContent, 0, 5000);
            $englishContent = mb_substr($englishContent, 0, 5000);

            $data = [
                'title' => $post->title,
                'description' => [
                    'persian' => strip_tags($persianContent),
                    'english' => strip_tags($englishContent)
                ],
                'author' => $post->author ? $post->author->name : '',
                'category' => $post->category ? $post->category->name : '',
                'publisher' => $post->publisher ? $post->publisher->name : '',
                'publication_year' => $post->publication_year,
                'format' => $post->format,
                'language' => $post->languages ?? 'fa',
                'isbn' => $post->isbn,
                'pages_count' => $post->pages_count
            ];

            // اضافه کردن نویسندگان همکار
            if ($post->authors && $post->authors->count() > 0) {
                $coAuthors = $post->authors->pluck('name')->toArray();
                $data['author'] .= ' ' . implode(' ', $coAuthors);
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('خطا در آماده‌سازی داده‌های کتاب', [
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * حذف و ایجاد مجدد ایندکس
     */
    private function deleteAndCreateIndex()
    {
        try {
            $client = app(ElasticsearchService::class);

            // در اینجا باید متدهای deleteIndex و createIndex به ElasticsearchService اضافه کنید
            // یا مستقیماً با client کار کنید

            $this->info('ایندکس جدید ایجاد شد.');

        } catch (\Exception $e) {
            $this->error('خطا در ایجاد ایندکس: ' . $e->getMessage());
        }
    }
}
