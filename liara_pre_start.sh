#!/bin/bash
echo "Running liara_pre_start hook..."

# انتشار کانفیگ Purifier (منتظر باشید تا دیسک متصل شود)
sleep 5
php artisan vendor:publish --provider="Mews\Purifier\PurifierServiceProvider" --force

# ایجاد دایرکتوری کش Purifier
echo "Creating purifier cache directory..."
mkdir -p /var/www/html/storage/app/purifier
chmod -R 755 /var/www/html/storage/app/purifier

echo "Purifier configuration published and cache directory created."
