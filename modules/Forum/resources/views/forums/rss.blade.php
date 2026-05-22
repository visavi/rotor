@extends('layout_rss')

@section('title', __('forums.title_rss'))

@section('content')
    @foreach ($topics as $topic)
        @if ($topic->lastPost->text)
            @php
                $postText = absolutizeUrls((string) $topic->lastPost->getText());
            @endphp

            <item>
                <title>{{ $topic->title }}</title>
                <link>{{ route('topics.topic', ['id' => $topic->id]) }}</link>
                <description>{{ $postText }}</description>
                <dc:creator>{{ $topic->lastPost->user->getName() }}</dc:creator>
                <pubDate>{{ date('r', $topic->updated_at) }}</pubDate>
                <category>{{ __('forums.topics') }}</category>
                <guid>{{ route('topics.topic', ['id' => $topic->id]) }}</guid>
            </item>
        @endif
    @endforeach
@stop
