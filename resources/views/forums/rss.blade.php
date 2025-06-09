@extends('layout_rss')

@section('title', __('forums.title_rss'))

@section('content')
    @foreach ($topics as $topic)
        @if ($topic->lastPost->text)
            <?php $postText = bbCode($topic->lastPost->text); ?>
            <?php $postText = str_replace('/uploads/stickers', asset('/uploads/stickers'), $postText); ?>

            <item>
                <title>{{ $topic->title }}</title>
                <link>{{ route('topics.topic', ['id' => $topic->id]) }}</link>
                <description>{{ $postText }}</description>
                <author>{{ $topic->lastPost->user->getName() }}</author>
                <pubDate>{{ date('r', $topic->updated_at) }}</pubDate>
                <category>{{ __('forums.topics') }}</category>
                <guid>{{ route('topics.topic', ['id' => $topic->id]) }}</guid>
            </item>
        @endif
    @endforeach
@stop
