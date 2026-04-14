@extends('layout_simple')

@section('title', $article->title)

@section('description', truncateDescription($article->getText(false)))

@section('content')
    <h1>{{ $article->title }}</h1>

    {{ $article->getText() }}

    <br><br>

    URL: <a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ route('articles.view', ['slug' => $article->slug]) }}</a>
@stop
