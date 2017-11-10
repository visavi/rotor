@extends('layout')

@section('title')
    Просмотр архива {{ $down->title }}
@stop

@section('content')
    <h1>Просмотр архива {{ $down->title }}</h1>

    @if ($content)
        <pre class="prettyprint linenums">{{ $content }}</pre><br>
    @else
        {{ showError('Файл пустой!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/down/{{ $down->id }}/zip">Вернуться</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/down/{{ $down->id }}">К файлу</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>
@stop
