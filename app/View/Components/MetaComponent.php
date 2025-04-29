<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MetaComponent extends Component
{
    public $title;
    public $description;
    public $image;
    public $url;
    public $type;
    public $publishedTime;
    public $modifiedTime;
    public $author;
    public $keywords;
    public $locale;
    public $siteName;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $title = null,
        $description = null,
        $image = null,
        $url = null,
        $type = 'article',
        $publishedTime = null,
        $modifiedTime = null,
        $author = null,
        $keywords = null,
        $locale = 'fa_IR',
        $siteName = 'کتابستان'
    ) {
        $this->title = $title ?: config('app.name', 'کتابستان');
        $this->description = $description ?: 'کتابستان - دنیای کتاب و کتابخوانی';
        $this->image = $image ?: asset('images/default-book.png');
        $this->url = $url ?: url()->current();
        $this->type = $type;
        $this->publishedTime = $publishedTime;
        $this->modifiedTime = $modifiedTime;
        $this->author = $author;
        $this->keywords = $keywords;
        $this->locale = $locale;
        $this->siteName = $siteName;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.meta-component');
    }
}
