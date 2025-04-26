#! /bin/bash
echo "Running liara_pre_start hook..."

# انتشار کانفیگ Purifier
php artisan vendor:publish --provider="Mews\Purifier\PurifierServiceProvider" --force

# ایجاد دایرکتوری کش Purifier
mkdir -p -m 0755 storage/app/purifier

echo "Purifier configuration published and cache directory created."
