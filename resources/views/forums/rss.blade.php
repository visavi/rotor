@extends('layout_rss')

@section('title')
    {{ trans('forums.title_rss') }}
@stop

@section('content')

    @foreach ($topics as $topic)
        <?php $topic->text = bbCode($topic->text); ?>
        <?php $topic->text = str_replace('/uploads/stickers', siteUrl().'/uploads/stickers', $topic->text); ?>

        <item>
            <title>{{ $topic->title }}</title>
            <link>{{ siteUrl() }}/topics/{{ $topic->id }}</link>
            <description>{{ $topic->text }} </description>
            <author>{{ $topic->lastPost->user->login }}</author>
            <pubDate>{{ date('r', $topic->updated_at) }}</pubDate>
            <category>{{ trans('forums.topics') }}</category>
            <guid>{{ siteUrl() }}/topics/{{ $topic->id }}</guid>
        </item>
    @endforeach
@stop
