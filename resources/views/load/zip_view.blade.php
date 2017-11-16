@extends('layout')

@section('title')
    Просмотр файла {{ $file->getName() }}
@stop

@section('content')
    <h1>Просмотр файла {{ $file->getName() }}</h1>

    Размер файла: {{ formatSize($file->getSize()) }}<hr>

    @if ($content)
        <pre class="prettyprint linenums">{{ $content }}</pre><br>
    @else
        {!! showError('Данный файл пустой!') !!}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/down/{{ $down->id }}/zip">Вернуться</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/down/{{ $down->id }}">К файлу</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>
@stop
