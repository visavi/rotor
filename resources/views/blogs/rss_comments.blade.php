@extends('layout_rss')

@section('title')
    {{ $article->title }}
@stop

@section('content')
    @foreach ($article->lastComments as $comment)
        <?php $comment->text = bbCode($comment->text); ?>
        <?php $comment->text = str_replace('/uploads/stickers', siteUrl().'/uploads/stickers', $comment->text); ?>

        <item>
            <title>{{ $comment->text }}</title>
            <link>{{ siteUrl() }}/articles/comments/{{ $article->id }}</link>
            <description>{{ $article->title }}</description>
            <author>{{ $comment->user->getName() }}</author>
            <pubDate>{{ date('r', $comment->created_at) }}</pubDate>
            <category>{{ __('main.comments') }}</category>
            <guid>{{ siteUrl() }}/articles/comment/{{ $article->id }}/{{ $comment->id }}</guid>
        </item>
    @endforeach
@stop

