@extends('layout_rss')

@section('title')
    {{ $down->title }}
@stop

@section('content')

    @foreach ($down->lastComments as $data)
        <?php $data->text = bbCode($data->text); ?>
        <?php $data->text = str_replace('/uploads/smiles', siteUrl().'/uploads/smiles', $data->text); ?>

        <item>
            <title>{{ $data->text }}</title>
            <link>{{ siteUrl() }}/down/{{ $down->id }}/comments</link>
            <description>{{ $down->title }}</description>
            <author>{{ $data->user->login }}</author>
            <pubDate>{{ date('r', $data->created_at) }}</pubDate>
            <category>Комментарии</category>
            <guid>{{ siteUrl() }}/down/{{ $down->id }}/comments?pid={{ $data->id }}</guid>
        </item>
    @endforeach
@stop

