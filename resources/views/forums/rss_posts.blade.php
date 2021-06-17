@extends('layout_rss')

@section('title', $topic->title)

@section('content')
    @foreach ($posts as $data)
        <?php $data->text = bbCode($data->text); ?>
        <?php $data->text = str_replace('/uploads/stickers', config('app.url').'/uploads/stickers', $data->text); ?>

        <item>
            <title>{{ $data->text }}</title>
            <link>{{ config('app.url') }}/topics/{{ $topic->id }}/{{ $data->id }}</link>
            <description>{{ $topic->title }} </description>
            <author>{{ $data->user->getName() }}</author>
            <pubDate>{{ date('r', $data->created_at) }}</pubDate>
            <category>{{ __('forums.posts') }}</category>
            <guid>{{ config('app.url') }}/topics/{{ $topic->id }}/{{  $data->id }}</guid>
        </item>
    @endforeach
@stop
