<?php

namespace Database\Seeders\Traits;

trait ImageUrlGenerator
{
    /**
     * Generate an image URL with the specified folder structure based on post ID
     *
     * @param int|null $postId Post ID to determine the folder
     * @return string
     */
    private function getRandomImageUrl(?int $postId = null): string
    {
        $imageFormats = ['jpg', 'png', 'webp'];
        $format = $imageFormats[array_rand($imageFormats)];

        $hash = md5(uniqid(rand(), true));

        if ($postId === null) {
            $postId = rand(1, 40000);
        }

        $folderBase = floor(($postId - 1) / 10000) * 10000;
        $folder = $folderBase === 0 ? "0" : (string)$folderBase;

        return "https://images.balyan.ir/{$folder}/{$hash}.{$format}";
    }
}
