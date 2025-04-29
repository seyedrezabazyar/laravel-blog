<!-- Primary Meta Tags -->
<title>{{ $title }}</title>
<meta name="title" content="{{ $title }}">
<meta name="description" content="{{ $description }}">
@if($keywords)
    <meta name="keywords" content="{{ $keywords }}">
@endif
<meta name="author" content="{{ $author ?? $siteName }}">
<meta name="language" content="Persian">
<meta name="robots" content="index, follow">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="canonical" href="{{ $url }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:locale" content="{{ $locale }}">
<meta property="og:site_name" content="{{ $siteName }}">
@if($publishedTime)
    <meta property="article:published_time" content="{{ $publishedTime->toIso8601String() }}">
@endif
@if($modifiedTime)
    <meta property="article:modified_time" content="{{ $modifiedTime->toIso8601String() }}">
@endif
@if($author)
    <meta property="article:author" content="{{ $author }}">
@endif

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $url }}">
<meta property="twitter:title" content="{{ $title }}">
<meta property="twitter:description" content="{{ $description }}">
<meta property="twitter:image" content="{{ $image }}">

<!-- Schema.org structured data for better AI indexing -->
<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "{{ $type == 'article' ? 'BlogPosting' : 'WebPage' }}",
    "headline": "{{ $title }}",
    "description": "{{ $description }}",
    "image": "{{ $image }}",
    "url": "{{ $url }}",
    "datePublished": "{{ $publishedTime ? $publishedTime->toIso8601String() : now()->toIso8601String() }}",
    "dateModified": "{{ $modifiedTime ? $modifiedTime->toIso8601String() : now()->toIso8601String() }}",
    "author": {
        "@type": "Person",
        "name": "{{ $author ?? $siteName }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ $siteName }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ asset('images/logo.png') }}"
        }
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ $url }}"
    }
}
</script>
