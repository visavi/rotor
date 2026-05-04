@extends('layout_rss')

@section('title', __('news.rss_title'))

@section('content')
    @foreach ($newses as $news)
        @php
            $newsText = absolutizeUrls((string) $news->getText());
        @endphp

        <item>
            <title>{{ $news->title }}</title>
            <link>{{ route('news.view', ['id' => $news->id]) }}</link>
            <description>{{ $newsText }}</description>
            <dc:creator>{{ $news->user->getName() }}</dc:creator>
            <pubDate>{{ date('r', $news->created_at) }}</pubDate>
            <category>{{ __('index.news') }}</category>
            <guid>{{ route('news.view', ['id' => $news->id]) }}</guid>
            @if ($news->files->isNotEmpty())
                <enclosure url="{{ $news->files->first()->getUrl() }}" length="{{ $news->files->first()->size }}" type="{{ $news->files->first()->mime_type }}" />
            @endif
        </item>
    @endforeach
@stop
