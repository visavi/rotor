@extends('layout_rss')

@section('title')
    {{ trans('loads.rss_downs') }}
@stop

@section('content')
    @foreach ($downs as $down)
        <?php $down->text = bbCode($down->text); ?>
        <?php $down->text = str_replace('/uploads/stickers', siteUrl().'/uploads/stickers', $down->text); ?>

        <item>
            <title>{{ $down->title }}</title>
            <link>{{ siteUrl() }}/down/{{ $down->id }}</link>
            <description>{{ $down->text }}</description>
            <author>{{ $down->user->login }}</author>
            <pubDate>{{ date('r', $down->created_at) }}</pubDate>
            <category>{{ trans('index.loads') }}</category>
            <guid>{{ siteUrl() }}/down/{{ $down->id }}</guid>
        </item>
    @endforeach
@stop
