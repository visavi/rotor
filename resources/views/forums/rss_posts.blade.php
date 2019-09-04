@extends('layout_rss')

@section('title')
    {{ $topic->title }}
@stop

@section('content')

    @foreach ($posts as $data)
        <?php $data->text = bbCode($data->text); ?>
        <?php $data->text = str_replace('/uploads/stickers', siteUrl().'/uploads/stickers', $data->text); ?>

        <item>
            <title>{{ $data->text }}</title>
            <link>{{ siteUrl() }}/topics/{{ $topic->id }}/{{ $data->id }}</link>
            <description>{{ $topic->title }} </description>
            <author>{{ $data->user->login }}</author>
            <pubDate>{{ date('r', $data->created_at) }}</pubDate>
            <category>{{ __('forums.posts') }}</category>
            <guid>{{ siteUrl() }}/topics/{{ $topic->id }}/{{  $data->id }}</guid>
        </item>
    @endforeach
@stop
