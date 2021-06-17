@extends('layout_rss')

@section('title', $down->title)

@section('content')
    @foreach ($down->lastComments as $data)
        <?php $data->text = bbCode($data->text); ?>
        <?php $data->text = str_replace('/uploads/stickers', config('app.url').'/uploads/stickers', $data->text); ?>

        <item>
            <title>{{ $data->text }}</title>
            <link>{{ config('app.url') }}/downs/comments/{{ $down->id }}</link>
            <description>{{ $down->title }}</description>
            <author>{{ $data->user->getName() }}</author>
            <pubDate>{{ date('r', $data->created_at) }}</pubDate>
            <category>{{ __('main.comments') }}</category>
            <guid>{{ config('app.url') }}/downs/comment/{{ $down->id }}/{{ $data->id }}</guid>
        </item>
    @endforeach
@stop

