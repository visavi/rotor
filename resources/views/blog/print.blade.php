@extends('layout_simple')

@section('title')
    {{ $blog->title }}
@stop

@section('content')

    <h1>{{ $blog->title }}</h1>

    {!! bbCode($blog->text) !!}

    <br><br>

    URL: <a href="{{ setting('home') }}/article/{{ $blog->id }}">{{ setting('home') }}/article/{{ $blog->id }}</a>
@stop
