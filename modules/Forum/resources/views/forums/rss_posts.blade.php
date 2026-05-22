@extends('layout_rss')

@section('title', $topic->title)

@section('content')
    @foreach ($posts as $data)
        @php
            $dataText = absolutizeUrls((string) $data->getText());
        @endphp

        <item>
            <title>{{ $dataText }}</title>
            <link>{{ route('topics.topic', ['id' => $topic->id, 'pid' => $data->id]) }}</link>
            <description>{{ $topic->title }} </description>
            <dc:creator>{{ $data->user->getName() }}</dc:creator>
            <pubDate>{{ date('r', $data->created_at) }}</pubDate>
            <category>{{ __('forums.posts') }}</category>
            <guid>{{ route('topics.topic', ['id' => $topic->id, 'pid' => $data->id]) }}</guid>
        </item>
    @endforeach
@stop
