@extends('layout_rss')

@section('title')
    {{ __('loads.rss_downs') }}
@stop

@section('content')
    @foreach ($downs as $down)
        <?php $down->text = bbCode($down->text); ?>
        <?php $down->text = str_replace('/uploads/stickers', siteUrl().'/uploads/stickers', $down->text); ?>

        <item>
            <title>{{ $down->title }}</title>
            <link>{{ siteUrl() }}/downs/{{ $down->id }}</link>
            <description>{{ $down->text }}</description>
            <author>{{ $down->user->getName() }}</author>
            <pubDate>{{ date('r', $down->created_at) }}</pubDate>
            <category>{{ __('index.loads') }}</category>
            <guid>{{ siteUrl() }}/downs/{{ $down->id }}</guid>
        </item>
    @endforeach
@stop
