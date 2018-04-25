@extends('layout_rss')

@section('title')
    RSS файлов
@stop

@section('content')

    @foreach ($downs as $down)
        <?php $down->text = bbCode($down->text); ?>
        <?php $down->text = str_replace('/uploads/smiles', siteUrl().'/uploads/smiles', $down->text); ?>

        <item>
            <title>{{ $down->title }}</title>
            <link>{{ siteUrl() }}/down/{{ $down->id }}</link>
            <description>{{ $down->text }}</description>
            <author>{{ $down->user->login }}</author>
            <pubDate>{{ date('r', $down->created_at) }}</pubDate>
            <category>Файлы</category>
            <guid>{{ siteUrl() }}/down/{{ $down->id }}</guid>
        </item>
    @endforeach
@stop
