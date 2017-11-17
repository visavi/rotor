@extends('layout_rss')

@section('title')
    RSS блогов
@stop

@section('content')

    @foreach ($blogs as $blog)
        <?php $blog->text = bbCode($blog->text); ?>
        <?php $blog->text = str_replace('/uploads/smiles', siteUrl().'/uploads/smiles', $blog->text); ?>

        <item>
            <title>{{ $blog->title }}</title>
            <link>{{ siteUrl() }}/article/{{ $blog->id }}</link>
            <description>{{ $blog->text }}</description>
            <author>{{ $blog->user->login }}</author>
            <pubDate>{{ date('r', $blog->created_at) }}</pubDate>
            <category>Блоги</category>
            <guid>{{ siteUrl() }}/article/{{ $blog->id }}</guid>
        </item>
    @endforeach
@stop
