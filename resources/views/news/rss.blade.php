@extends('layout_rss')

@section('title', __('news.rss_title'))

@section('content')
    @foreach ($newses as $news)
        @php
            $news->text = bbCode($news->text);
            $news->text = str_replace('/uploads/stickers', asset('/uploads/stickers'), $news->text);
        @endphp

        <item>
            <title>{{ $news->title }}</title>
            <link>{{ route('news.view', ['id' => $news->id]) }}</link>
            <description>{{ $news->text }}</description>
            <author>{{ $news->user->getName() }}</author>
            <pubDate>{{ date('r', $news->created_at) }}</pubDate>
            <category>{{ __('index.news') }}</category>
            <guid>{{ route('news.view', ['id' => $news->id]) }}</guid>
            @if ($news->files->isNotEmpty())
                <enclosure url="{{ $news->files->first()->getUrl() }}" length="{{ $news->files->first()->size }}" type="{{ $news->files->first()->mime_type }}" />
            @endif
        </item>
    @endforeach
@stop
