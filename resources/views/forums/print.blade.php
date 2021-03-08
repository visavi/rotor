@extends('layout_simple')

@section('title', $topic->title)

@section('description', $description)

@section('content')
    <h1>{{ $topic->title }}</h1>

    @foreach ($posts as $key => $data)
        {{ $key + 1 }}. <b>{{ $data->user->getName() }}</b> ({{ dateFixed($data->created_at) }})<br>
        {{ bbCode($data->text) }}
        <br><br>
    @endforeach

    URL: <a href="{{ siteUrl() }}/topics/{{ $topic->id }}">{{ siteUrl(true) }}/topics/{{ $topic->id }}</a>
@stop
