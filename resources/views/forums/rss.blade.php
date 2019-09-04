@extends('layout_rss')

@section('title')
    {{ __('forums.title_rss') }}
@stop

@section('content')

    @foreach ($topics as $topic)
        @if ($topic->lastPost->text)
            <?php $postText = bbCode($topic->lastPost->text); ?>
            <?php $postText = str_replace('/uploads/stickers', siteUrl().'/uploads/stickers', $postText); ?>

            <item>
                <title>{{ $topic->title }}</title>
                <link>{{ siteUrl() }}/topics/{{ $topic->id }}</link>
                <description>{{ $postText }}</description>
                <author>{{ $topic->lastPost->user->login }}</author>
                <pubDate>{{ date('r', $topic->updated_at) }}</pubDate>
                <category>{{ __('forums.topics') }}</category>
                <guid>{{ siteUrl() }}/topics/{{ $topic->id }}</guid>
            </item>
        @endif
    @endforeach
@stop
