@extends('layout_rss')

@section('title')
    Темы форума
@stop

@section('content')

    @foreach ($topics as $topic)
        <?php $topic->text = bbCode($topic->text); ?>
        <?php $topic->text = str_replace('/uploads/smiles', siteUrl().'/uploads/smiles', $topic->text); ?>

        <item>
            <title>{{ $topic->title }}</title>
            <link>{{ siteUrl() }}/topic/{{ $topic->id }}</link>
            <description>{{ $topic->text }} </description>
            <author>{{ $topic->lastPost->user->login }}</author>
            <pubDate>{{ date('r', $topic->updated_at) }}</pubDate>
            <category>Темы</category>
            <guid>{{ siteUrl() }}/topic/{{ $topic->id }}</guid>
        </item>
    @endforeach
@stop
