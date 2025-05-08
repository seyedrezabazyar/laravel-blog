<?php

return [
    // Main CDN host for images
    'image_host' => env('CDN_IMAGE_HOST', 'https://images.balyan.ir'),

    // Fallback image
    'default_book_image' => env('DEFAULT_BOOK_IMAGE', '/images/default-book.png'),

    // Cache settings
    'cache_duration' => [
        'blog_post' => env('CACHE_DURATION_BLOG_POST', 60), // minutes
        'blog_index' => env('CACHE_DURATION_BLOG_INDEX', 30), // minutes
        'categories' => env('CACHE_DURATION_CATEGORIES', 60 * 24), // 1 day
    ],

    // Image sizes for responsive images
    'image_sizes' => [
        'thumbnail' => ['width' => 300, 'height' => 400],
        'medium' => ['width' => 600, 'height' => 800],
        'large' => ['width' => 900, 'height' => 1200],
    ],
];
