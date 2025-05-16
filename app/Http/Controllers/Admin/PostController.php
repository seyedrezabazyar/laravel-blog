<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter');
        $postsQuery = Post::query()->select('id', 'title', 'slug', 'is_published', 'hide_content', 'created_at');

        switch ($filter) {
            case 'published':
                $postsQuery->where('is_published', true);
                break;
            case 'draft':
                $postsQuery->where('is_published', false);
                break;
            case 'hidden':
                $postsQuery->where('hide_content', true);
                break;
        }

        $postsQuery->orderBy('id', 'desc');
        $cacheKey = 'posts_counts_' . md5(json_encode($request->all()));

        $counts = Cache::remember($cacheKey, 600, function () {
            return [
                'published' => Post::where('is_published', true)->count(),
                'draft' => Post::where('is_published', false)->count(),
                'hidden' => Post::where('hide_content', true)->count(),
            ];
        });

        $posts = $postsQuery->paginate(15);

        return view('admin.posts.index', [
            'posts' => $posts,
            'publishedCount' => $counts['published'],
            'draftCount' => $counts['draft'],
            'hiddenCount' => $counts['hidden']
        ]);
    }

    public function edit($id)
    {
        try {
            $post = Post::select([
                'id', 'title', 'slug', 'english_title', 'content', 'english_content',
                'category_id', 'author_id', 'publisher_id', 'language',
                'publication_year', 'format', 'book_codes', 'purchase_link',
                'is_published', 'hide_content'
            ])->findOrFail($id);

            $featuredImage = PostImage::where('post_id', $id)
                ->select('id', 'image_path', 'hide_image')
                ->orderBy('sort_order')
                ->first();

            $tags_list = DB::table('post_tag')
                ->join('tags', 'post_tag.tag_id', '=', 'tags.id')
                ->where('post_tag.post_id', $id)
                ->pluck('tags.name')
                ->implode(', ');

            $post_authors = DB::table('post_author')
                ->where('post_id', $id)
                ->pluck('author_id')
                ->toArray();

            $categories = Category::select('id', 'name')->orderBy('name')->get();
            $authors = Author::select('id', 'name')->orderBy('name')->get();
            $publishers = Publisher::select('id', 'name')->orderBy('name')->get();

            return view('admin.posts.edit', compact(
                'post', 'featuredImage', 'tags_list', 'categories',
                'authors', 'post_authors', 'publishers'
            ));
        } catch (\Exception $e) {
            Log::error('Error in edit post form: ' . $e->getMessage(), [
                'post_id' => $id,
            ]);

            return redirect()->route('admin.posts.index')
                ->with('error', 'خطا در بارگذاری فرم ویرایش: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        // تغییر وضعیت انتشار یا نمایش
        if ($request->has('toggle_publish') || $request->has('toggle_visibility')) {
            try {
                $currentPage = $request->input('current_page', 1);
                $currentFilter = $request->input('current_filter', '');

                if ($request->has('toggle_publish')) {
                    DB::statement("UPDATE posts SET is_published = NOT is_published WHERE id = ?", [$id]);
                    $isPublished = DB::scalar("SELECT is_published FROM posts WHERE id = ?", [$id]);
                    $statusMessage = $isPublished ? 'منتشر' : 'به پیش‌نویس منتقل';
                }

                if ($request->has('toggle_visibility')) {
                    DB::statement("UPDATE posts SET hide_content = NOT hide_content WHERE id = ?", [$id]);
                    $isHidden = DB::scalar("SELECT hide_content FROM posts WHERE id = ?", [$id]);
                    $statusMessage = $isHidden ? 'مخفی' : 'قابل نمایش';
                }

                $this->clearCaches($id);
                $title = $request->input('title', 'پست');
                $queryParams = [];

                if (!empty($currentFilter)) $queryParams['filter'] = $currentFilter;
                if ($currentPage > 1) $queryParams['page'] = $currentPage;

                $redirectUrl = route('admin.posts.index');
                if (!empty($queryParams)) {
                    $redirectUrl .= '?' . http_build_query($queryParams);
                }

                return redirect($redirectUrl)
                    ->with('success', "کتاب «{$title}» با موفقیت {$statusMessage} شد.");
            } catch (\Exception $e) {
                Log::error('خطا در تغییر وضعیت پست: ' . $e->getMessage(), ['post_id' => $id]);
                return redirect()->back()->with('error', 'خطا در به‌روزرسانی وضعیت پست: ' . $e->getMessage());
            }
        }

        // به‌روزرسانی کامل پست
        try {
            $validated = $request->validate([
                'title' => 'required|max:255',
                'english_title' => 'nullable|max:255',
                'content' => 'required',
                'english_content' => 'nullable',
                'category_id' => 'required|exists:categories,id',
                'author_id' => 'nullable|exists:authors,id',
                'publisher_id' => 'nullable|exists:publishers,id',
                'language' => 'nullable|max:50',
                'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
                'format' => 'nullable|max:50',
                'book_codes' => 'nullable',
                'purchase_link' => 'nullable|url',
                'is_published' => 'boolean',
                'hide_content' => 'boolean',
                'hide_image' => 'nullable|boolean',
                'authors' => 'nullable|array',
                'authors.*' => 'exists:authors,id',
                'tags' => 'nullable|string|max:500',
                'image' => 'nullable|image|max:2048',
            ]);

            $postData = $validated;
            unset($postData['authors'], $postData['tags'], $postData['image'], $postData['hide_image']);
            $postData['slug'] = Str::slug($validated['title']);

            DB::beginTransaction();

            // پاکسازی محتوا
            if (!empty($postData['content'])) {
                $postData['content'] = Purifier::clean($postData['content']);
            }
            if (!empty($postData['english_content'])) {
                $postData['english_content'] = Purifier::clean($postData['english_content']);
            }

            // به‌روزرسانی پست
            $post = Post::findOrFail($id);
            $post->update($postData);

            // پردازش تصویر
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('post_images', 'public');
                $featuredImage = $post->featuredImage;

                if ($featuredImage) {
                    $featuredImage->update(['image_path' => $path]);
                } else {
                    PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => $path,
                        'sort_order' => 0
                    ]);
                }
            }

            // به‌روزرسانی وضعیت نمایش تصویر
            if (isset($validated['hide_image']) && $post->featuredImage) {
                $post->featuredImage->update([
                    'hide_image' => $validated['hide_image'] ? 'hidden' : 'visible'
                ]);
            }

            $this->updateAuthorsAndTags($post, $validated);

            DB::commit();
            $this->clearCaches($id);

            return redirect()->route('admin.posts.index')
                ->with('success', 'کتاب با موفقیت بروزرسانی شد.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطا در به‌روزرسانی پست: ' . $e->getMessage(), ['post_id' => $id]);
            return redirect()->back()->withInput()
                ->with('error', 'خطا در به‌روزرسانی پست: ' . $e->getMessage());
        }
    }

    private function updateAuthorsAndTags($post, $validated)
    {
        // به‌روزرسانی نویسندگان
        if (isset($validated['authors'])) {
            DB::table('post_author')->where('post_id', $post->id)->delete();

            if (!empty($validated['authors'])) {
                $mainAuthorId = reset($validated['authors']);
                $post->update(['author_id' => $mainAuthorId]);

                $authorData = [];
                foreach ($validated['authors'] as $authorId) {
                    $authorData[] = [
                        'post_id' => $post->id,
                        'author_id' => $authorId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                if (!empty($authorData)) {
                    DB::table('post_author')->insert($authorData);
                }
            }
        }

        // به‌روزرسانی تگ‌ها
        if (isset($validated['tags'])) {
            DB::table('post_tag')->where('post_id', $post->id)->delete();

            if (!empty($validated['tags'])) {
                $tags = explode(',', $validated['tags']);

                foreach ($tags as $tagName) {
                    $tagName = trim($tagName);
                    if (empty($tagName)) continue;

                    $slug = Str::slug($tagName);
                    $tag = DB::table('tags')->where('slug', $slug)->first();

                    if (!$tag) {
                        $tagId = DB::table('tags')->insertGetId([
                            'name' => $tagName,
                            'slug' => $slug,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    } else {
                        $tagId = $tag->id;
                    }

                    DB::table('post_tag')->insert([
                        'post_id' => $post->id,
                        'tag_id' => $tagId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    private function clearCaches($id)
    {
        $cacheKeys = [
            "post_edit_{$id}_minimal_data",
            "post_edit_{$id}_content_data",
            "post_{$id}_featured_image_minimal",
            "post_{$id}_tags_string",
            "post_{$id}_coauthors_ids",
            "post_{$id}_featured_image_id",
            "admin_posts_page_1",
            "home_latest_posts",
            "post_{$id}_related_posts_admin",
            "post_{$id}_related_posts_user"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // پاک کردن کش‌های وابسته به روابط
        $relatedInfo = DB::select("SELECT category_id, author_id, publisher_id FROM posts WHERE id = ? LIMIT 1", [$id]);
        if (!empty($relatedInfo) && isset($relatedInfo[0])) {
            $info = $relatedInfo[0];

            if (!empty($info->category_id)) {
                Cache::forget("category_posts_{$info->category_id}_page_1_admin");
                Cache::forget("category_posts_{$info->category_id}_page_1_user");
            }

            if (!empty($info->author_id)) {
                Cache::forget("author_posts_{$info->author_id}_page_1_admin");
                Cache::forget("author_posts_{$info->author_id}_page_1_user");
            }

            if (!empty($info->publisher_id)) {
                Cache::forget("publisher_posts_{$info->publisher_id}_page_1_admin");
                Cache::forget("publisher_posts_{$info->publisher_id}_page_1_user");
            }
        }

        // پاک کردن کش شمارشگرها
        Cache::forget('posts_counts');
    }
}
