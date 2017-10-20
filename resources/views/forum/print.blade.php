@extends('layout_simple')

@section('title')
    {{ $topic->title }}
@stop

@section('content')
    <h1>{{ $topic->title }}</h1>

    @foreach ($posts as $key => $data)
        {{ ($key + 1) }}. <b>{{ $data->user->login }}</b> ({{ dateFixed($data->created_at) }})<br>
        {!! bbCode($data->text) !!}
        <br><br>
    @endforeach

    URL: <a href="{{ siteUrl() }}/topic/{{ $topic->id }}">{{ siteUrl(true) }}/topic/{{ $topic->id }}</a>
@stop
