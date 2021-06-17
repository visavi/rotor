@extends('layout_rss')

@section('title', __('forums.title_rss'))

@section('content')
    @foreach ($topics as $topic)
        @if ($topic->lastPost->text)
            <?php $postText = bbCode($topic->lastPost->text); ?>
            <?php $postText = str_replace('/uploads/stickers', config('app.url').'/uploads/stickers', $postText); ?>

            <item>
                <title>{{ $topic->title }}</title>
                <link>{{ config('app.url') }}/topics/{{ $topic->id }}</link>
                <description>{{ $postText }}</description>
                <author>{{ $topic->lastPost->user->getName() }}</author>
                <pubDate>{{ date('r', $topic->updated_at) }}</pubDate>
                <category>{{ __('forums.topics') }}</category>
                <guid>{{ config('app.url') }}/topics/{{ $topic->id }}</guid>
            </item>
        @endif
    @endforeach
@stop
