<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="fa-IR">
    <title>{{ $title }}</title>
    <subtitle>{{ $description }}</subtitle>
    <link href="{{ url()->full() }}" rel="self"/>
    <link href="{{ url('/') }}"/>
    <updated>{{ $posts->first()->created_at->toAtomString() }}</updated>
    <id>{{ url('/') }}</id>
    <author>
        <n>کتابستان</n>
    </author>

    @foreach ($posts as $post)
        <entry>
            <title>{{ $post->title }}</title>
            <link href="{{ route('blog.show', $post->slug) }}"/>
            <id>{{ route('blog.show', $post->slug) }}</id>
            <published>{{ $post->created_at->toAtomString() }}</published>
            <updated>{{ $post->updated_at->toAtomString() }}</updated>

            @if($post->author)
                <author>
                    <n>{{ $post->author->name }}</n>
                    <uri>{{ route('blog.author', $post->author->slug) }}</uri>
                </author>
            @endif

            <category term="{{ $post->category->slug }}" label="{{ $post->category->name }}"/>

            @foreach($post->tags as $tag)
                <category term="{{ $tag->slug }}" label="{{ $tag->name }}"/>
            @endforeach

            <summary type="html">
                <![CDATA[
                @if($post->featuredImage && !$post->featuredImage->hide_image)
                    <img src="{{ $post->featuredImage->display_url }}" alt="{{ $post->title }}" width="300"/>
                @endif
                {{ \Illuminate\Support\Str::limit(strip_tags($post->content), 300) }}
                ]]>
            </summary>

            <content type="html">
                <![CDATA[
                @if($post->featuredImage && !$post->featuredImage->hide_image)
                    <p><img src="{{ $post->featuredImage->display_url }}" alt="{{ $post->title }}" style="max-width:100%; height:auto;"/></p>
                @endif
                {!! $post->content !!}
                ]]>
            </content>
        </entry>
    @endforeach
</feed>
