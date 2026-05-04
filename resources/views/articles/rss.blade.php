@extends('layout_rss')

@section('title', __('blogs.title_rss'))

@section('content')
    @foreach ($articles as $article)
        @php
            $articleText = absolutizeUrls((string) $article->getText());
        @endphp

        <item>
            <title>{{ $article->title }}</title>
            <link>{{ route('articles.view', ['slug' => $article->slug]) }}</link>
            <description>{{ $articleText }}</description>
            <dc:creator>{{ $article->user->getName() }}</dc:creator>
            <pubDate>{{ date('r', $article->created_at) }}</pubDate>
            <category>{{ __('index.blogs') }}</category>
            <guid>{{ route('articles.view', ['slug' => $article->slug]) }}</guid>
        </item>
    @endforeach
@stop
