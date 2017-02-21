@extends('layout_rss')

@section('title')
    {{ $topic['title'] }} - @parent
@stop

@section('content')

    @foreach ($posts as $data)
        <?php $data['text'] = App::bbCode($data['text']); ?>
        <?php $data['text'] = str_replace('/uploads/smiles', $config['home'].'/uploads/smiles', $data['text']); ?>

        <item>
            <title>{{ $data['text'] }}</title>
            <link>{{ App::setting('home') }}/topic/{{ $topic['id'] }}</link>
            <description>{{ $topic['title'] }} </description>
            <author>{{ $data->getUser()->login }}</author>
            <pubDate>{{ date("r", $data['created_at']) }}</pubDate>
            <category>Сообщения</category>
            <guid>{{ App::setting('home') }}/topic/{{ $topic['id'] }}/{{  $data['id'] }}</guid>
        </item>
    @endforeach
@stop
