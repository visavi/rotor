@extends('layout_rss')

@section('title', $article->title)

@section('content')
    @foreach ($article->lastComments as $comment)
        @php
            $commentText = absolutizeUrls((string) $comment->getText());
        @endphp

        <item>
            <title>{{ $commentText }}</title>
            <link>{{ route('articles.view', ['slug' => $article->slug]) }}</link>
            <description>{{ $article->title }}</description>
            <dc:creator>{{ $comment->user->getName() }}</dc:creator>
            <pubDate>{{ date('r', $comment->created_at) }}</pubDate>
            <category>{{ __('main.comments') }}</category>
            <guid>{{ route('articles.view', ['slug' => $article->slug, 'cid' => $comment->id]) }}</guid>
        </item>
    @endforeach
@stop
