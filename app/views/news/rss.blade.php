@extends('layout_rss')

@section('title')
    RSS новостей - @parent
@stop

@section('content')

    @foreach ($newses as $news):
        <?php $news['text'] = App::bbCode($news['text']); ?>
        <?php $news['text'] = str_replace(['/uploads/smiles', '[cut]'], [Setting::get('home').'/uploads/smiles', ''], $news['text']); ?>

        <item>
            <title>{{ $news['title'] }}</title>
            <link>{{  Setting::get('home') }}/news/{{ $news['id'] }}</link>
            <description>{{ $news['text'] }}</description>
            <author>{{ $news->getUser()->login }}</author>
            <pubDate>{{ date("r", $news['created_at']) }}</pubDate>
            <category>Новости</category>
            <guid>{{ Setting::get('home') }}/news/{{ $news['id'] }}</guid>
        </item>
    @endforeach
@stop
