<?php

namespace App\View\Components;

use App\Services\ImageUrlService;
use Illuminate\View\Component;

class ResponsiveImage extends Component
{
    public $post;
    public $image;
    public $alt;
    public $size;
    public $cssClass;
    public $lazy;
    public $showPlaceholder;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $post = null,
        $image = null,
        $alt = '',
        $size = 'medium',
        $cssClass = 'w-full h-full object-cover',
        $lazy = true,
        $showPlaceholder = true
    ) {
        $this->post = $post;
        $this->image = $image;
        $this->alt = $alt;
        $size = $size;
        $this->cssClass = $cssClass;
        $this->lazy = $lazy;
        $this->showPlaceholder = $showPlaceholder;
    }

    /**
     * دریافت آدرس تصویر اصلی
     */
    public function getImageUrl()
    {
        if ($this->image) {
            return $this->image->getImageUrlWithSize($this->size);
        }

        if ($this->post && $this->post->md5) {
            return $this->post->getFeaturedImageUrlWithSize($this->size);
        }

        return ImageUrlService::getDefaultImageUrl();
    }

    /**
     * دریافت آدرس‌های responsive
     */
    public function getResponsiveUrls()
    {
        if ($this->image) {
            return $this->image->responsive_urls;
        }

        if ($this->post && $this->post->md5) {
            return $this->post->featured_image_responsive_urls;
        }

        $defaultUrl = ImageUrlService::getDefaultImageUrl();
        return [
            'thumbnail' => $defaultUrl,
            'small' => $defaultUrl,
            'medium' => $defaultUrl,
            'large' => $defaultUrl,
            'original' => $defaultUrl,
        ];
    }

    /**
     * تولید srcset
     */
    public function getSrcset()
    {
        $urls = $this->getResponsiveUrls();

        return implode(', ', [
            $urls['small'] . ' 300w',
            $urls['medium'] . ' 600w',
            $urls['large'] . ' 900w',
        ]);
    }

    /**
     * دریافت متن alt
     */
    public function getAltText()
    {
        if (!empty($this->alt)) {
            return $this->alt;
        }

        if ($this->post && $this->post->title) {
            return $this->post->title;
        }

        return 'تصویر کتاب';
    }

    /**
     * آدرس تصویر placeholder
     */
    public function getPlaceholderUrl()
    {
        if (!$this->showPlaceholder) {
            return null;
        }

        if ($this->post && $this->post->md5) {
            return ImageUrlService::getCachedImageUrl($this->post->id, $this->post->md5) . '?w=20&blur=5';
        }

        return null;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.responsive-image');
    }
}
