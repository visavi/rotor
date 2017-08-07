@extends('layout_rss')

@section('title')
    {{ $topic['title'] }} - @parent
@stop

@section('content')

    @foreach ($posts as $data)
        <?php $data['text'] = App::bbCode($data['text']); ?>
        <?php $data['text'] = str_replace('/uploads/smiles', Setting::get('home').'/uploads/smiles', $data['text']); ?>

        <item>
            <title>{{ $data['text'] }}</title>
            <link>{{ Setting::get('home') }}/topic/{{ $topic['id'] }}/{{  $data['id'] }}</link>
            <description>{{ $topic['title'] }} </description>
            <author>{{ $data->getUser()->login }}</author>
            <pubDate>{{ date("r", $data['created_at']) }}</pubDate>
            <category>Сообщения</category>
            <guid>{{ Setting::get('home') }}/topic/{{ $topic['id'] }}/{{  $data['id'] }}</guid>
        </item>
    @endforeach
@stop
