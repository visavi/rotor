@extends('layout_rss')

@section('title')
    {{ $topic->title }}
@stop

@section('content')

    @foreach ($posts as $data)
        <?php $data->text = bbCode($data->text); ?>
        <?php $data->text = str_replace('/uploads/smiles', siteUrl().'/uploads/smiles', $data->text); ?>

        <item>
            <title>{{ $data->text }}</title>
            <link>{{ siteUrl() }}/topic/{{ $topic->id }}/{{ $data->id }}</link>
            <description>{{ $topic->title }} </description>
            <author>{{ $data->user->login }}</author>
            <pubDate>{{ date("r", $data->created_at) }}</pubDate>
            <category>Сообщения</category>
            <guid>{{ siteUrl() }}/topic/{{ $topic->id }}/{{  $data->id }}</guid>
        </item>
    @endforeach
@stop
