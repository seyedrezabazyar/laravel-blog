<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
        try {
            $filename = $this->generateUniqueFilename($file);
            $path = trim($directory, '/') . '/' . $filename;

            // استفاده از مسیر نسبی به جای مسیر مطلق
            $stream = fopen($file->getRealPath(), 'r+');
            $result = Storage::disk(config('filesystems.cloud', 'download_host'))->put($path, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }

            if ($result) {
                Log::info('File uploaded to download host', [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName()
                ]);
                return $path;
            }

            Log::warning('Failed to upload file to download host', [
                'file' => $file->getClientOriginalName()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error uploading file to download host', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
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

        // اگر URL کامل باشد، فقط مسیر را استخراج کنید
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            $host = parse_url($path, PHP_URL_HOST);
            $pathOnly = parse_url($path, PHP_URL_PATH);

            if ($host === 'images.balyan.ir') {
                $path = ltrim($pathOnly, '/');
            } else {
                // اگر هاست متفاوت است، نمی‌توان فایل را حذف کرد
                return false;
            }
        }

        try {
            return Storage::disk('download_host')->delete($path);
        } catch (\Exception $e) {
            Log::error('Error deleting file from download host', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * دریافت URL دانلود فایل
     *
     * @param string $path
     * @return string
     */
    public function url(string $path)
    {
        // استفاده از دامنه سفارشی از تنظیمات
        $baseUrl = config('app.custom_image_host', 'https://images.balyan.ir');

        // اگر URL کامل باشد، آن را برگردانید
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }

        // اگر مسیر با images.balyan.ir شروع شود
        if (strpos($path, 'images.balyan.ir/') !== false) {
            return 'https://' . $path;
        }

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
