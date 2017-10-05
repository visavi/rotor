@extends('layout_rss')

@section('title')
    {{ $topic['title'] }}
@stop

@section('content')

    @foreach ($posts as $data)
        <?php $data['text'] = bbCode($data['text']); ?>
        <?php $data['text'] = str_replace('/uploads/smiles', setting('home').'/uploads/smiles', $data['text']); ?>

        <item>
            <title>{{ $data['text'] }}</title>
            <link>{{ setting('home') }}/topic/{{ $topic['id'] }}/{{  $data['id'] }}</link>
            <description>{{ $topic['title'] }} </description>
            <author>{{ $data->user->login }}</author>
            <pubDate>{{ date("r", $data['created_at']) }}</pubDate>
            <category>Сообщения</category>
            <guid>{{ setting('home') }}/topic/{{ $topic['id'] }}/{{  $data['id'] }}</guid>
        </item>
    @endforeach
@stop
