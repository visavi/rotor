@extends('layout_rss')

@section('title', $topic->title)

@section('content')
    @foreach ($posts as $data)
        @php
            $data->text = bbCode($data->text);
            $data->text = str_replace('/uploads/stickers', asset('/uploads/stickers'), $data->text);
            $topic->title = bbCode($topic->title);
            $topic->title = str_replace('/uploads/stickers', asset('/uploads/stickers'), $topic->title);
        @endphp

        <item>
            <title>{{ $data->text }}</title>
            <link>{{ route('topics.topic', ['id' => $topic->id, 'pid' => $data->id]) }}</link>
            <description>{{ $topic->title }} </description>
            <author>{{ $data->user->getName() }}</author>
            <pubDate>{{ date('r', $data->created_at) }}</pubDate>
            <category>{{ __('forums.posts') }}</category>
            <guid>{{ route('topics.topic', ['id' => $topic->id, 'pid' => $data->id]) }}</guid>
        </item>
    @endforeach
@stop
