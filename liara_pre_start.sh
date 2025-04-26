#!/bin/bash
echo "Running liara_pre_start hook..."

# انتظار برای اتصال دیسک
sleep 10

# انتشار کانفیگ Purifier
php artisan vendor:publish --provider="Mews\Purifier\PurifierServiceProvider" --force

# ایجاد دایرکتوری کش Purifier در مسیر framework/cache
echo "Creating purifier cache directory..."
mkdir -p /var/www/html/storage/framework/cache/purifier
chmod -R 777 /var/www/html/storage/framework/cache/purifier

echo "Purifier configuration completed."
