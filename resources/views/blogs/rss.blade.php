@extends('layout_rss')

@section('title', __('blogs.title_rss'))

@section('content')
    @foreach ($articles as $article)
        @php
            $article->text = bbCode($article->text);
            $article->text = str_replace('/uploads/stickers', asset('/uploads/stickers'), $article->text);
            $article->text = str_replace('/uploads/articles', asset('/uploads/articles'), $article->text);
        @endphp

        <item>
            <title>{{ $article->title }}</title>
            <link>{{ route('articles.view', ['slug' => $article->slug]) }}</link>
            <description>{{ $article->text }}</description>
            <author>{{ $article->user->getName() }}</author>
            <pubDate>{{ date('r', $article->created_at) }}</pubDate>
            <category>{{ __('index.blogs') }}</category>
            <guid>{{ route('articles.view', ['slug' => $article->slug]) }}</guid>
        </item>
    @endforeach
@stop
