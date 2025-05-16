<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GalleryController extends Controller
{
    // نمایش همه تصاویر با وضعیت null و با pagination
    public function index()
    {
        $images = PostImage::whereNull('hide_image')->orderBy('id', 'asc')->paginate(100);
        return view('admin.images.gallery', compact('images'));
    }

    // تایید تصویر (تغییر به وضعیت visible)
    public function approve(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update([
            'hide_image' => 'visible',
            'approved_at' => now()
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تصویر تأیید شد.']);
        }

        return redirect()->back()->with('success', 'تصویر با موفقیت تأیید شد.');
    }

    // تایید گروهی تصاویر
    public function bulkApprove(Request $request)
    {
        $imageIds = $request->input('image_ids', []);
        if (!empty($imageIds)) {
            PostImage::whereIn('id', $imageIds)->update([
                'hide_image' => 'visible',
                'approved_at' => now()
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'همه تصاویر تأیید شدند.']);
            }

            return redirect()->back()->with('success', 'همه تصاویر با موفقیت تأیید شدند.');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'هیچ تصویری برای تأیید وجود ندارد.']);
        }

        return redirect()->back()->with('error', 'هیچ تصویری برای تأیید وجود ندارد.');
    }

    // رد تصویر (تغییر به وضعیت hidden)
    public function reject(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update(['hide_image' => 'hidden']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تصویر رد شد.']);
        }

        return redirect()->back()->with('success', 'تصویر با موفقیت رد شد.');
    }

    // علامت‌گذاری تصویر به عنوان گمشده
    public function markMissing(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update(['hide_image' => 'missing']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'تصویر به عنوان گمشده علامت‌گذاری شد.']);
        }

        return redirect()->back()->with('success', 'تصویر با موفقیت به عنوان گمشده علامت‌گذاری شد.');
    }

    // بازگرداندن تصویر به حالت null
    public function reset(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update(['hide_image' => null]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'وضعیت تصویر بازنشانی شد.']);
        }

        return redirect()->back()->with('success', 'وضعیت تصویر با موفقیت بازنشانی شد.');
    }

    // نمایش تصاویر تأیید شده (visible)
    public function visible()
    {
        $images = PostImage::where('hide_image', 'visible')->orderBy('id', 'asc')->paginate(100);
        return view('admin.images.visible', compact('images'));
    }

    // نمایش تصاویر رد شده (hidden)
    public function hidden()
    {
        $images = PostImage::where('hide_image', 'hidden')->orderBy('id', 'asc')->paginate(100);
        return view('admin.images.hidden', compact('images'));
    }

    // نمایش تصاویر گمشده (missing)
    public function missing()
    {
        $images = PostImage::where('hide_image', 'missing')->orderBy('id', 'asc')->paginate(100);
        return view('admin.images.missing', compact('images'));
    }

    // بررسی وضعیت تصاویر و علامت‌گذاری تصاویر گمشده
    public function checkMissingImages()
    {
        $images = PostImage::whereNull('hide_image')->orderBy('id', 'asc')->take(50)->get();
        $count = 0;

        foreach ($images as $image) {
            try {
                $imageUrl = $image->image_url ?? $image->image_path;
                if (!empty($imageUrl)) {
                    $response = Http::timeout(3)->get($imageUrl);
                    if ($response->status() !== 200) {
                        $image->update(['hide_image' => 'missing']);
                        $count++;
                    }
                } else {
                    $image->update(['hide_image' => 'missing']);
                    $count++;
                }
            } catch (\Exception $e) {
                $image->update(['hide_image' => 'missing']);
                $count++;
            }
        }

        return redirect()->back()->with('success', $count . ' تصویر به عنوان گمشده علامت‌گذاری شد.');
    }
}
