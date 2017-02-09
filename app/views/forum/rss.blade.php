@extends('layout_rss')

@section('title')
    Темы форума - @parent
@stop

@section('content')

    @foreach ($topics as $topic)
        <?php $topic['text'] = App::bbCode($topic['text']); ?>
        <?php $topic['text'] = str_replace('/uploads/smiles', $config['home'].'/uploads/smiles', $topic['text']); ?>

        <item>
            <title>{{ $topic['title'] }}</title>
            <link>{{ App::setting('home') }}/topic/{{ $topic['topic_id'] }}</link>
            <description>{{ $topic['text'] }} </description>
            <author>{{ $topic->getLastPost()->getUser()->login }}</author>
            <pubDate>{{ date("r", $topic['time']) }}</pubDate>
            <category>Темы</category>
            <guid>{{ App::setting('home') }}/topic/{{ $topic['topic_id'] }}</guid>
        </item>
    @endforeach
@stop
