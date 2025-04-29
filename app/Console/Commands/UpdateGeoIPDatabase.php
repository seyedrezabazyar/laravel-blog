<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use ZipArchive;

class UpdateGeoIPDatabase extends Command
{
    /**
     * نام و سیگنچر دستور.
     *
     * @var string
     */
    protected $signature = 'geoip:update';

    /**
     * توضیحات دستور.
     *
     * @var string
     */
    protected $description = 'Download and update the MaxMind GeoIP database';

    /**
     * مسیر ذخیره فایل دیتابیس.
     *
     * @var string
     */
    protected $dbPath;

    /**
     * مسیر ذخیره فایل موقت.
     *
     * @var string
     */
    protected $tempPath;

    /**
     * اجرای دستور.
     *
     * @return int
     */
    public function handle()
    {
        $this->dbPath = storage_path('app/geoip');
        $this->tempPath = storage_path('app/temp');

        // ایجاد دایرکتوری‌ها اگر وجود ندارند
        if (!File::exists($this->dbPath)) {
            File::makeDirectory($this->dbPath, 0755, true);
        }

        if (!File::exists($this->tempPath)) {
            File::makeDirectory($this->tempPath, 0755, true);
        }

        $accountId = config('services.maxmind.account_id');
        $licenseKey = config('services.maxmind.license_key');

        if (empty($accountId) || empty($licenseKey)) {
            $this->error('MaxMind account ID or license key is not configured. Check your .env file.');
            return 1;
        }

        $this->info('Downloading MaxMind GeoIP database...');

        $tempFile = $this->tempPath . '/GeoLite2-Country.tar.gz';

        try {
            // دانلود فایل از MaxMind
            $downloadUrl = "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key={$licenseKey}&suffix=tar.gz";

            $response = Http::withOptions([
                'sink' => $tempFile,
            ])->get($downloadUrl);

            if (!$response->successful()) {
                $this->error('Failed to download the database: ' . $response->status());
                return 1;
            }

            $this->info('Database downloaded successfully.');
            $this->info('Extracting files...');

            // استخراج فایل فشرده
            $process = proc_open("tar -xzf {$tempFile} -C {$this->tempPath}", [], $pipes);
            proc_close($process);

            // پیدا کردن و انتقال فایل دیتابیس
            $extractedDir = glob($this->tempPath . '/GeoLite2-Country_*')[0] ?? null;

            if (!$extractedDir || !is_dir($extractedDir)) {
                $this->error('Could not find extracted directory.');
                return 1;
            }

            $mmdbFile = glob($extractedDir . '/GeoLite2-Country.mmdb')[0] ?? null;

            if (!$mmdbFile || !file_exists($mmdbFile)) {
                $this->error('Could not find mmdb file in extracted directory.');
                return 1;
            }

            // کپی فایل به محل اصلی
            File::copy($mmdbFile, $this->dbPath . '/GeoLite2-Country.mmdb');

            // پاکسازی فایل‌های موقت
            File::deleteDirectory($extractedDir);
            File::delete($tempFile);

            $this->info('MaxMind GeoIP database has been updated successfully!');

            return 0;
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());

            // پاکسازی فایل‌های موقت در صورت وجود
            if (File::exists($tempFile)) {
                File::delete($tempFile);
            }

            return 1;
        }
    }
}
