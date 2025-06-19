@extends('layout_simple')

@section('title', $article->title)

@section('description', truncateDescription(bbCode($article->text, false)))

@section('content')
    <h1>{{ $article->title }}</h1>

    {{ bbCode($article->text) }}

    <br><br>

    URL: <a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ route('articles.view', ['slug' => $article->slug]) }}</a>
@stop
