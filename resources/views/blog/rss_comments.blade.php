@extends('layout_rss')

@section('title')
    {{ $blog->title }}
@stop

@section('content')

    @foreach ($blog->lastComments as $data)
        <?php $data->text = bbCode($data->text); ?>
        <?php $data->text = str_replace('/uploads/smiles', siteUrl().'/uploads/smiles', $data->text); ?>

        <item>
            <title>{{ $data->text }}</title>
            <link>{{ siteUrl() }}/article/{{ $blog->id }}/comments</link>
            <description>{{ $blog->title }}</description>
            <author>{{ $data->user->login }}</author>
            <pubDate>{{ date("r", $data->created_at) }}</pubDate>
            <category>Комментарии</category>
            <guid>{{ siteUrl() }}/article/{{ $blog->id }}/comments?pid={{ $data->id }}</guid>
        </item>
    @endforeach
@stop

