@extends('layout_rss')

@section('title')
    {{ trans('blogs.title_rss') }}
@stop

@section('content')

    @foreach ($blogs as $blog)
        <?php $blog->text = bbCode($blog->text); ?>
        <?php $blog->text = str_replace('/uploads/stickers', siteUrl().'/uploads/stickers', $blog->text); ?>

        <item>
            <title>{{ $blog->title }}</title>
            <link>{{ siteUrl() }}/articles/{{ $blog->id }}</link>
            <description>{{ $blog->text }}</description>
            <author>{{ $blog->user->login }}</author>
            <pubDate>{{ date('r', $blog->created_at) }}</pubDate>
            <category>{{ trans('blogs.blogs') }}</category>
            <guid>{{ siteUrl() }}/articles/{{ $blog->id }}</guid>
        </item>
    @endforeach
@stop
