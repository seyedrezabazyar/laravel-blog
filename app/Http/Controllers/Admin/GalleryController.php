<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GalleryController extends Controller
{
    // نمایش همه تصاویر بدون فیلتر hide_image و با pagination
    public function index()
    {
        $images = Image::paginate(4);
        return view('admin.images.gallery', compact('images'));
    }

    // نمایش تصاویری که کد وضعیت 200 دارند و hide_image = null
    public function real()
    {
        $page = request()->get('page', 1);
        $images = Image::whereNull('hide_image')->paginate(4, ['*'], 'page', $page);
        $validImages = [];

        $responses = Http::pool(function ($pool) use ($images) {
            foreach ($images as $image) {
                $pool->as($image->id)->timeout(5)->get($image->image_path);
            }
        });

        foreach ($images as $image) {
            if (isset($responses[$image->id]) && $responses[$image->id]->status() === 200) {
                $validImages[] = $image;
            }
        }

        while (empty($validImages) && $images->hasMorePages()) {
            $page++;
            $images = Image::whereNull('hide_image')->paginate(4, ['*'], 'page', $page);
            $validImages = [];

            $responses = Http::pool(function ($pool) use ($images) {
                foreach ($images as $image) {
                    $pool->as($image->id)->timeout(5)->get($image->image_path);
                }
            });

            foreach ($images as $image) {
                if (isset($responses[$image->id]) && $responses[$image->id]->status() === 200) {
                    $validImages[] = $image;
                }
            }
        }

        return view('admin.gallery.real', compact('images', 'validImages'));
    }

    // API برای تأیید تصویر (visible)
    public function approve(Request $request, $id)
    {
        $image = Image::findOrFail($id);
        $image->update(['hide_image' => 'visible']);
        return response()->json(['success' => true, 'message' => 'تصویر تأیید شد.']);
    }

    // API برای تأیید گروهی تصاویر صفحه فعلی
    public function bulkApprove(Request $request)
    {
        $imageIds = $request->input('image_ids', []);
        if (!empty($imageIds)) {
            Image::whereIn('id', $imageIds)->update(['hide_image' => 'visible']);
            return response()->json(['success' => true, 'message' => 'همه تصاویر تأیید شدند.']);
        }
        return response()->json(['success' => false, 'message' => 'هیچ تصویری برای تأیید وجود ندارد.']);
    }

    // API برای رد تصویر (hidden)
    public function reject(Request $request, $id)
    {
        $image = Image::findOrFail($id);
        $image->update(['hide_image' => 'hidden']);
        return response()->json(['success' => true, 'message' => 'تصویر رد شد.']);
    }

    // نمایش تصاویر تأیید شده (visible)
    public function visible()
    {
        $images = Image::where('hide_image', 'visible')->paginate(4);
        return view('admin.gallery.visible', compact('images'));
    }

    // نمایش تصاویر رد شده (hidden)
    public function hidden()
    {
        $images = Image::where('hide_image', 'hidden')->paginate(4);
        return view('admin.gallery.hidden', compact('images'));
    }
}
