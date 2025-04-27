<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class DownloadHostService
{
    /**
     * آپلود فایل به هاست دانلود
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string|false
     */
    public function upload(UploadedFile $file, string $directory = '')
    {
        $filename = $this->generateUniqueFilename($file);
        $path = trim($directory, '/') . '/' . $filename;

        $stream = fopen($file->getRealPath(), 'r+');
        $result = Storage::disk('download_host')->put($path, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if ($result) {
            return $path;
        }

        return false;
    }

    /**
     * حذف فایل از هاست دانلود
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path)
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk('download_host')->delete($path);
    }

    /**
     * دریافت URL دانلود فایل
     *
     * @param string $path
     * @return string
     */
    public function url(string $path)
    {
        // استفاده از دامنه سفارشی اگر تنظیم شده باشد
        $baseUrl = config('app.custom_image_host', 'https://images.balyan.ir');

        // حذف اسلش های اضافی
        $path = ltrim($path, '/');

        return $baseUrl . '/' . $path;
    }

    /**
     * تولید نام فایل یکتا
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateUniqueFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = Str::slug($name); // نام فایل را اسلاگ کن

        // ایجاد یک نام فایل یکتا با استفاده از timestamp
        return $name . '_' . time() . '_' . uniqid() . '.' . $extension;
    }
}
