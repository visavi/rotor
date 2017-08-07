@extends('layout_rss')

@section('title')
    {{ $blog['title'] }} - @parent
@stop

@section('content')

    @foreach ($blog->lastComments as $data)
        <?php $data['text'] = App::bbCode($data['text']); ?>
        <?php $data['text'] = str_replace('/uploads/smiles', Setting::get('home').'/uploads/smiles', $data['text']); ?>

        <item>
            <title>{{ $data['text'] }}</title>
            <link>{{ Setting::get('home') }}/article/{{ $blog['id'] }}/comments</link>
            <description>{{ $blog['title'] }}</description>
            <author>{{ $data->getUser()->login }}</author>
            <pubDate>{{ date("r", $data['created_at']) }}</pubDate>
            <category>Комментарии</category>
            <guid>{{ Setting::get('home') }}/article/{{ $blog['id'] }}/comments?pid={{ $data['id'] }}</guid>
        </item>
    @endforeach
@stop

