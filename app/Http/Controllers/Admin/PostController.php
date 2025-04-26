<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\PostImage;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'category', 'author', 'publisher'])->latest()->paginate(10);
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();
        $tags = Tag::orderBy('name')->get();
        return view('admin.posts.create', compact('categories', 'authors', 'publishers', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'english_title' => 'nullable|max:255',
            'content' => 'required',
            'english_content' => 'nullable',
            'category_id' => 'required|exists:categories,id',
            'author_id' => 'nullable|exists:authors,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'featured_image' => 'nullable|image|max:2048',
            'language' => 'nullable|max:50',
            'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'format' => 'nullable|max:50',
            'book_codes' => 'nullable',
            'tags' => 'nullable|string',
            'purchase_link' => 'nullable|url',
            'is_published' => 'boolean',
            'hide_image' => 'boolean',
            'hide_content' => 'boolean',
            'co_authors' => 'nullable|array',
            'co_authors.*' => 'exists:authors,id',
            'post_images' => 'nullable|array',
            'post_images.*' => 'image|max:2048',
            'image_captions' => 'nullable|array',
            'hide_post_images' => 'nullable|array',
            'hide_post_images.*' => 'boolean',
        ]);

        // تنظیم مقادیر پیش‌فرض
        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']);
        $validated['md5_hash'] = md5($validated['title'] . time()); // ایجاد یک هش منحصر به فرد

        // پاکسازی محتوا قبل از ذخیره
        $validated['content'] = Purifier::clean($validated['content']);
        if (isset($validated['english_content'])) {
            $validated['english_content'] = Purifier::clean($validated['english_content']);
        }

        // ذخیره تصویر شاخص
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('posts', 'public');
            $validated['featured_image'] = $path;
        }

        // حذف فیلدهای اضافی از آرایه اعتبارسنجی شده
        $coAuthors = $request->input('co_authors', []);
        $postImages = $request->file('post_images', []);
        $imageCaptions = $request->input('image_captions', []);
        $hidePostImages = $request->input('hide_post_images', []);
        $tags = null;

        if (isset($validated['tags'])) {
            $tags = $validated['tags'];
            unset($validated['tags']);
        }

        // حذف فیلدهای اضافی که مستقیماً در مدل ذخیره نمی‌شوند
        unset($validated['co_authors']);
        unset($validated['post_images']);
        unset($validated['image_captions']);
        unset($validated['hide_post_images']);

        // ایجاد پست
        $post = Post::create($validated);

        // اضافه کردن نویسندگان همکار
        if (!empty($coAuthors)) {
            $post->authors()->attach($coAuthors);
        }

        // همگام‌سازی برچسب‌ها
        if ($tags !== null) {
            $this->syncTags($post, $tags);
        }

        // ذخیره تصاویر اضافی
        if (!empty($postImages)) {
            foreach ($postImages as $index => $image) {
                $imagePath = $image->store('post_images', 'public');
                $caption = isset($imageCaptions[$index]) ? $imageCaptions[$index] : null;
                $hideImage = isset($hidePostImages[$index]) ? true : false;

                PostImage::create([
                    'post_id' => $post->id,
                    'image_path' => $imagePath,
                    'caption' => $caption,
                    'hide_image' => $hideImage,
                    'sort_order' => $index
                ]);
            }
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'پست با موفقیت ایجاد شد.');
    }

    public function show(Post $post)
    {
        $post->load(['user', 'category', 'author', 'publisher', 'authors', 'images', 'tags']);
        return view('admin.posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        $authors = Author::all();
        $publishers = Publisher::all();
        $coAuthors = $post->authors->pluck('id')->toArray();
        $tags = Tag::orderBy('name')->get();
        return view('admin.posts.edit', compact('post', 'categories', 'authors', 'publishers', 'coAuthors', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'english_title' => 'nullable|max:255',
            'content' => 'required',
            'english_content' => 'nullable',
            'category_id' => 'required|exists:categories,id',
            'author_id' => 'nullable|exists:authors,id',
            'publisher_id' => 'nullable|exists:publishers,id',
            'featured_image' => 'nullable|image|max:2048',
            'language' => 'nullable|max:50',
            'publication_year' => 'nullable|integer|min:1800|max:' . date('Y'),
            'format' => 'nullable|max:50',
            'book_codes' => 'nullable',
            'tags' => 'nullable|string',
            'purchase_link' => 'nullable|url',
            'is_published' => 'boolean',
            'hide_image' => 'boolean',
            'hide_content' => 'boolean',
            'co_authors' => 'nullable|array',
            'co_authors.*' => 'exists:authors,id',
            'post_images' => 'nullable|array',
            'post_images.*' => 'image|max:2048',
            'image_captions' => 'nullable|array',
            'hide_post_images' => 'nullable|array',
            'hide_post_images.*' => 'boolean',
            'existing_image_captions' => 'nullable|array',
            'delete_images' => 'nullable|array',
            'hide_existing_images' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        // پاکسازی محتوا قبل از به‌روزرسانی
        $validated['content'] = Purifier::clean($validated['content']);
        if (isset($validated['english_content'])) {
            $validated['english_content'] = Purifier::clean($validated['english_content']);
        }

        // ذخیره تصویر شاخص
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $path = $request->file('featured_image')->store('posts', 'public');
            $validated['featured_image'] = $path;
        }

        // ذخیره اطلاعات اضافی
        $coAuthors = $request->input('co_authors', []);
        $postImages = $request->file('post_images', []);
        $imageCaptions = $request->input('image_captions', []);
        $hidePostImages = $request->input('hide_post_images', []);
        $existingImageCaptions = $request->input('existing_image_captions', []);
        $deleteImages = $request->input('delete_images', []);
        $hideExistingImages = $request->input('hide_existing_images', []);
        $tags = null;

        if (isset($validated['tags'])) {
            $tags = $validated['tags'];
            unset($validated['tags']);
        }

        // حذف فیلدهای اضافی که مستقیماً در مدل ذخیره نمی‌شوند
        unset($validated['co_authors']);
        unset($validated['post_images']);
        unset($validated['image_captions']);
        unset($validated['hide_post_images']);
        unset($validated['existing_image_captions']);
        unset($validated['delete_images']);
        unset($validated['hide_existing_images']);

        // به‌روزرسانی پست
        $post->update($validated);

        // به‌روزرسانی نویسندگان همکار
        $post->authors()->sync($coAuthors);

        // همگام‌سازی برچسب‌ها
        if ($tags !== null) {
            $this->syncTags($post, $tags);
        }

        // به‌روزرسانی تصاویر موجود
        if (!empty($existingImageCaptions)) {
            foreach ($existingImageCaptions as $imageId => $caption) {
                $image = PostImage::find($imageId);
                if ($image && $image->post_id == $post->id) {
                    $image->caption = $caption;
                    $image->hide_image = in_array($imageId, $hideExistingImages ?? []);
                    $image->save();
                }
            }
        }

        // حذف تصاویر انتخاب شده
        if (!empty($deleteImages)) {
            foreach ($deleteImages as $imageId) {
                $image = PostImage::find($imageId);
                if ($image && $image->post_id == $post->id) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }
        }

        // افزودن تصاویر جدید
        if (!empty($postImages)) {
            $lastSortOrder = $post->images()->max('sort_order') ?? 0;

            foreach ($postImages as $index => $image) {
                $imagePath = $image->store('post_images', 'public');
                $caption = isset($imageCaptions[$index]) ? $imageCaptions[$index] : null;
                $hideImage = isset($hidePostImages[$index]) ? true : false;

                PostImage::create([
                    'post_id' => $post->id,
                    'image_path' => $imagePath,
                    'caption' => $caption,
                    'hide_image' => $hideImage,
                    'sort_order' => $lastSortOrder + $index + 1
                ]);
            }
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'پست با موفقیت بروزرسانی شد.');
    }

    public function destroy(Post $post)
    {
        // حذف تصویر شاخص
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        // حذف تصاویر اضافی
        foreach ($post->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        // حذف رابطه با نویسندگان همکار
        $post->authors()->detach();

        // حذف رابطه با برچسب‌ها
        $post->tags()->detach();

        // حذف پست
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'پست با موفقیت حذف شد.');
    }

    /**
     * همگام‌سازی برچسب‌های پست
     *
     * @param Post $post
     * @param string $tagsString
     * @return void
     */
    private function syncTags(Post $post, string $tagsString)
    {
        // جداسازی برچسب‌ها با کاما
        $tagNames = array_map('trim', explode(',', $tagsString));
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                );
                $tagIds[] = $tag->id;
            }
        }

        // همگام‌سازی برچسب‌ها با پست
        $post->tags()->sync($tagIds);
    }
}
