@extends('layout_rss')

@section('title', __('blogs.title_rss'))

@section('content')
    @foreach ($articles as $article)
        <?php $article->text = bbCode($article->text); ?>
        <?php $article->text = str_replace('/uploads/stickers', config('app.url') . '/uploads/stickers', $article->text); ?>

        <item>
            <title>{{ $article->title }}</title>
            <link>{{ config('app.url') }}/articles/{{ $article->id }}</link>
            <description>{{ $article->text }}</description>
            <author>{{ $article->user->getName() }}</author>
            <pubDate>{{ date('r', $article->created_at) }}</pubDate>
            <category>{{ __('index.blogs') }}</category>
            <guid>{{ config('app.url') }}/articles/{{ $article->id }}</guid>
        </item>
    @endforeach
@stop
