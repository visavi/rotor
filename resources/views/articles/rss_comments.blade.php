@extends('layout_rss')

@section('title', $article->title)

@section('content')
    @foreach ($article->lastComments as $comment)
        @php
            $comment->text = $comment->getText();
            $comment->text = str_replace('/uploads/stickers', asset('/uploads/stickers'), $comment->text);
        @endphp

        <item>
            <title>{{ $comment->text }}</title>
            <link>{{ route('articles.view', ['slug' => $article->slug]) }}</link>
            <description>{{ $article->title }}</description>
            <author>{{ $comment->user->getName() }}</author>
            <pubDate>{{ date('r', $comment->created_at) }}</pubDate>
            <category>{{ __('main.comments') }}</category>
            <guid>{{ route('articles.view', ['slug' => $article->slug, 'cid' => $comment->id]) }}</guid>
        </item>
    @endforeach
@stop
