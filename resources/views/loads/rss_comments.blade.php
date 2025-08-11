@extends('layout_rss')

@section('title', $down->title)

@section('content')
    @foreach ($down->lastComments as $data)
        @php
            $data->text = bbCode($data->text);
            $data->text = str_replace('/uploads/stickers', asset('/uploads/stickers'), $data->text);
        @endphp

        <item>
            <title>{{ $data->text }}</title>
            <link>{{ route('downs.comments', ['id' => $down->id]) }}</link>
            <description>{{ $down->title }}</description>
            <author>{{ $data->user->getName() }}</author>
            <pubDate>{{ date('r', $data->created_at) }}</pubDate>
            <category>{{ __('main.comments') }}</category>
            <guid>{{ route('downs.comments', ['id' => $down->id, 'cid' => $data->id]) }}</guid>
        </item>
    @endforeach
@stop
