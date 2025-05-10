<?php

use GuzzleHttp\RequestOptions;
use Spatie\Sitemap\Crawler\Profile;

return [
    /*
     * گزینه‌های زیر به GuzzleHttp\Client منتقل می‌شود وقتی که ایجاد می‌شود.
     * برای همه گزینه‌های ممکن، به داکیومنت‌های Guzzle مراجعه کنید.
     *
     * http://docs.guzzlephp.org/en/stable/request-options.html
     */
    'guzzle_options' => [
        RequestOptions::COOKIES => true,
        RequestOptions::CONNECT_TIMEOUT => 10,
        RequestOptions::TIMEOUT => 30,
        RequestOptions::ALLOW_REDIRECTS => true,
    ],

    /*
     * تعداد URLهایی که در یک نقشه سایت واحد نمایش داده می‌شوند
     * مقدار حداکثر استاندارد 50000 است
     */
    'max_tags_per_sitemap' => 50000,

    /*
     * فشرده‌سازی gzip برای فایل‌های نقشه سایت
     */
    'use_gzip' => false,

    /*
     * آیا اجرای جاوااسکریپت باید فعال باشد
     */
    'execute_javascript' => false,

    /*
     * این بسته حدس هوشمندانه‌ای در مورد محل نصب Google Chrome می‌زند.
     * شما همچنین می‌توانید به صورت دستی موقعیت آن را اینجا مشخص کنید.
     */
    'chrome_binary_path' => '',

    /*
     * ژنراتور نقشه سایت از یک پیاده‌سازی CrawlProfile استفاده می‌کند
     * تا تعیین کند کدام URLها برای نقشه سایت خزش شوند.
     */
    'crawl_profile' => Profile::class,
];
