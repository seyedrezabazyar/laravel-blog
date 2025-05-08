<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageService
{
    /**
     * Default cache time in minutes
     */
    protected $cacheTime = 60;

    /**
     * Get optimized image URL with proper caching
     *
     * @param string|null $imagePath Original image path
     * @param int $width Desired width
     * @param int $height Desired height
     * @param string $fit Fit method (crop, contain, etc.)
     * @param bool $forceCrop Whether to force exact dimensions
     * @return string The optimized image URL
     */
    public function getOptimizedUrl(?string $imagePath, int $width = 300, int $height = 400, string $fit = 'crop', bool $forceCrop = false): string
    {
        // Return default image if no path provided
        if (empty($imagePath)) {
            return asset(config('cdn.default_book_image', '/images/default-book.png'));
        }

        // Generate a unique cache key for this image request
        $cacheKey = "img_" . md5($imagePath . $width . $height . $fit . ($forceCrop ? '1' : '0'));

        // Try to get URL from cache
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($imagePath, $width, $height, $fit, $forceCrop) {
            // Handle external URLs vs local storage
            if (Str::startsWith($imagePath, ['http://', 'https://'])) {
                // For external URLs, we use a proxy service or CDN parameters if available
                if (Str::contains($imagePath, config('cdn.image_host'))) {
                    // If it's our own CDN, we can append parameters
                    return $this->appendCdnParams($imagePath, $width, $height, $fit);
                }

                // For other external URLs, just return as is
                return $imagePath;
            }

            // For local storage images, optimize and cache
            return $this->optimizeLocalImage($imagePath, $width, $height, $fit, $forceCrop);
        });
    }

    /**
     * Optimize a local image and return its URL
     */
    protected function optimizeLocalImage(string $imagePath, int $width, int $height, string $fit, bool $forceCrop): string
    {
        // Generate optimized image path
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION) ?: 'jpg';
        $optimizedPath = 'optimized/' . Str::slug(pathinfo($imagePath, PATHINFO_FILENAME))
            . "_{$width}x{$height}_{$fit}." . $extension;

        // Check if optimized version already exists
        if (!Storage::disk('public')->exists($optimizedPath)) {
            try {
                // Load the original image
                $img = Image::make(Storage::disk('public')->path($imagePath));

                // Resize based on fit method
                if ($fit === 'crop' && $forceCrop) {
                    $img->fit($width, $height);
                } elseif ($fit === 'contain') {
                    $img->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } else {
                    // Default resize with aspect ratio
                    $img->resize($width, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

                // Optimize the image (reduce quality slightly for JPG)
                if (in_array(strtolower($extension), ['jpg', 'jpeg'])) {
                    $img->encode('jpg', 85); // 85% quality
                }

                // Save the optimized image
                Storage::disk('public')->put($optimizedPath, $img->stream());

            } catch (\Exception $e) {
                // If optimization fails, return original
                return Storage::url($imagePath);
            }
        }

        // Return the URL to the optimized image
        return Storage::url($optimizedPath);
    }

    /**
     * Append CDN parameters for on-the-fly image optimization
     */
    protected function appendCdnParams(string $url, int $width, int $height, string $fit): string
    {
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Different CDNs have different parameter formats
        if (Str::contains($parsedUrl['host'] ?? '', 'imagekit')) {
            // ImageKit format
            return $url . "?tr=w-{$width},h-{$height},f-{$fit}";
        } elseif (Str::contains($parsedUrl['host'] ?? '', 'cloudinary')) {
            // Cloudinary format
            $transformations = "w_{$width},h_{$height},c_" . ($fit === 'crop' ? 'fill' : 'fit');
            $pathParts = explode('upload/', $url);
            return $pathParts[0] . 'upload/' . $transformations . '/' . ($pathParts[1] ?? '');
        } elseif (Str::contains($parsedUrl['host'] ?? '', 'balyan.ir')) {
            // Custom format for balyan.ir
            $separator = (Str::contains($url, '?')) ? '&' : '?';
            return $url . "{$separator}width={$width}&height={$height}&fit={$fit}";
        }

        // Generic parameter format
        $separator = (Str::contains($url, '?')) ? '&' : '?';
        return $url . "{$separator}w={$width}&h={$height}&fit={$fit}";
    }

    /**
     * Create responsive image markup with srcset
     */
    public function getResponsiveImage(string $imagePath, string $alt, array $sizes = null): string
    {
        $sizes = $sizes ?? config('cdn.image_sizes', [
            'thumbnail' => ['width' => 300, 'height' => 400],
            'medium' => ['width' => 600, 'height' => 800],
            'large' => ['width' => 900, 'height' => 1200],
        ]);

        $srcset = [];
        $defaultSrc = '';

        foreach ($sizes as $size => $dimensions) {
            $url = $this->getOptimizedUrl($imagePath, $dimensions['width'], $dimensions['height']);
            $srcset[] = "{$url} {$dimensions['width']}w";

            // Use medium size as default
            if ($size === 'medium') {
                $defaultSrc = $url;
            }
        }

        // If no medium size, use the first available
        if (empty($defaultSrc) && !empty($srcset)) {
            $defaultSrc = explode(' ', $srcset[0])[0];
        }

        // Set default if still empty
        if (empty($defaultSrc)) {
            $defaultSrc = asset(config('cdn.default_book_image', '/images/default-book.png'));
        }

        // Create the responsive image HTML
        return '<img
            src="' . $defaultSrc . '"
            srcset="' . implode(', ', $srcset) . '"
            sizes="(max-width: 768px) 100vw, 50vw"
            alt="' . htmlspecialchars($alt) . '"
            loading="lazy"
            class="w-full h-full object-cover"
            onerror="this.onerror=null;this.src=\'' . asset('images/default-book.png') . '\';"
        >';
    }

    /**
     * Get placeholder image URL with blurred preview
     */
    public function getPlaceholderUrl(?string $imagePath, int $width = 20): string
    {
        if (empty($imagePath)) {
            return asset(config('cdn.default_book_image', '/images/default-book.png'));
        }

        $cacheKey = "placeholder_" . md5($imagePath . $width);

        return Cache::remember($cacheKey, $this->cacheTime * 24, function () use ($imagePath, $width) {
            try {
                // For local storage images
                if (!Str::startsWith($imagePath, ['http://', 'https://'])) {
                    $img = Image::make(Storage::disk('public')->path($imagePath));
                } else {
                    // For remote images
                    $img = Image::make($imagePath);
                }

                // Generate tiny placeholder
                $img->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                // Blur it slightly
                $img->blur(5);

                // Convert to base64 for immediate display
                return 'data:image/jpeg;base64,' . base64_encode($img->encode('jpg', 60)->encoded);

            } catch (\Exception $e) {
                return asset(config('cdn.default_book_image', '/images/default-book.png'));
            }
        });
    }
}
