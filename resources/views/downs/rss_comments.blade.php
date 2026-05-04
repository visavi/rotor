@extends('layout_rss')

@section('title', $down->title)

@section('content')
    @foreach ($down->lastComments as $data)
        @php
            $dataText = absolutizeUrls((string) $data->getText());
        @endphp

        <item>
            <title>{{ $dataText }}</title>
            <link>{{ route('downs.view', ['id' => $down->id]) }}</link>
            <description>{{ $down->title }}</description>
            <dc:creator>{{ $data->user->getName() }}</dc:creator>
            <pubDate>{{ date('r', $data->created_at) }}</pubDate>
            <category>{{ __('main.comments') }}</category>
            <guid>{{ route('downs.view', ['id' => $down->id, 'cid' => $data->id]) }}</guid>
        </item>
    @endforeach
@stop
