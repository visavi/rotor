@extends('layout_rss')

@section('title')
    RSS блогов - @parent
@stop

@section('content')

    @foreach ($blogs as $blog)
        <?php $blog['text'] = bbCode($blog['text']); ?>
        <?php $blog['text'] = str_replace('/uploads/smiles', setting('home').'/uploads/smiles', $blog['text']); ?>

        <item>
            <title>{{ $blog['title'] }}</title>
            <link>{{  setting('home') }}/article/{{ $blog['id'] }}</link>
            <description>{{ $blog['text'] }}</description>
            <author>{{ $blog->getUser()->login }}</author>
            <pubDate>{{ date("r", $blog['created_at']) }}</pubDate>
            <category>Блоги</category>
            <guid>{{ setting('home') }}/article/{{ $blog['id'] }}</guid>
        </item>
    @endforeach
@stop
