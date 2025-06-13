@extends('layout_simple')

@section('title', $topic->title)

@section('description', $description)

@section('content')
    <h1>{{ $topic->title }}</h1>

    @foreach ($posts as $key => $data)
        {{ $key + 1 }}. <b>{{ $data->user->getName() }}</b> <small class="section-date text-muted fst-italic">{{ dateFixed($data->created_at) }}</small><br>
        {{ bbCode($data->text) }}
        <br><br>
    @endforeach

    URL: <a href="{{ route('topics.topic', ['id' => $topic->id]) }}">{{ route('topics.topic', ['id' => $topic->id]) }}</a>
@stop
