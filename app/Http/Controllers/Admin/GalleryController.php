<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GalleryController extends Controller
{
    public function index()
    {
        $images = PostImage::whereNull('hide_image')
            ->orderBy('id', 'asc')
            ->paginate(100);

        return view('admin.images.gallery', compact('images'));
    }

    public function approve(Request $request, $id)
    {
        try {
            $image = PostImage::findOrFail($id);
            $image->update([
                'hide_image' => 'visible',
                'updated_at' => now()
            ]);

            Log::info('تصویر تأیید شد', [
                'user_id' => auth()->id(),
                'image_id' => $id,
                'post_id' => $image->post_id,
            ]);

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => 'تصویر تأیید شد.'])
                : redirect()->back()->with('success', 'تصویر با موفقیت تأیید شد.');

        } catch (\Exception $e) {
            Log::error('خطا در تأیید تصویر', [
                'image_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'خطا در تأیید تصویر: ' . $e->getMessage()], 500)
                : redirect()->back()->with('error', 'خطا در تأیید تصویر: ' . $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        $imageIds = $request->input('image_ids', []);
        if (!empty($imageIds)) {
            PostImage::whereIn('id', $imageIds)->update([
                'hide_image' => 'visible',
                'updated_at' => now()
            ]);

            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'همه تصاویر تأیید شدند.', 'count' => count($imageIds)])
                : redirect()->back()->with('success', 'همه تصاویر با موفقیت تأیید شدند.');
        }

        return $request->wantsJson()
            ? response()->json(['success' => false, 'message' => 'هیچ تصویری برای تأیید وجود ندارد.'])
            : redirect()->back()->with('error', 'هیچ تصویری برای تأیید وجود ندارد.');
    }

    public function reject(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update(['hide_image' => 'hidden']);

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => 'تصویر رد شد.'])
            : redirect()->back()->with('success', 'تصویر با موفقیت رد شد.');
    }

    public function markMissing(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update(['hide_image' => 'missing']);

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => 'تصویر به عنوان گمشده علامت‌گذاری شد.'])
            : redirect()->back()->with('success', 'تصویر با موفقیت به عنوان گمشده علامت‌گذاری شد.');
    }

    public function reset(Request $request, $id)
    {
        $image = PostImage::findOrFail($id);
        $image->update(['hide_image' => null]);

        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => 'وضعیت تصویر بازنشانی شد.'])
            : redirect()->back()->with('success', 'وضعیت تصویر با موفقیت بازنشانی شد.');
    }

    public function visible()
    {
        $images = PostImage::where('hide_image', 'visible')
            ->orderBy('updated_at', 'desc')
            ->paginate(100);

        return view('admin.images.visible', compact('images'));
    }

    public function hidden()
    {
        $images = PostImage::where('hide_image', 'hidden')
            ->orderBy('updated_at', 'desc')
            ->paginate(100);

        return view('admin.images.hidden', compact('images'));
    }

    public function missing()
    {
        $images = PostImage::where('hide_image', 'missing')
            ->orderBy('updated_at', 'desc')
            ->paginate(100);

        return view('admin.images.missing', compact('images'));
    }
}
