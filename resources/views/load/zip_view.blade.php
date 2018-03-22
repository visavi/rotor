@extends('layout')

@section('title')
    Просмотр файла {{ $file->getName() }}
@stop

@section('content')
    <h1>Просмотр файла {{ $file->getName() }}</h1>

    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>

        @if ($down->category->parent->id)
            <li class="breadcrumb-item"><a href="/load/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
        @endif

        <li class="breadcrumb-item"><a href="/load/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
        <li class="breadcrumb-item"><a href="/down/{{ $down->id }}">{{ $down->title }}</a></li>
        <li class="breadcrumb-item"><a href="/down/zip/{{ $down->id }}">Архив</a></li>
        <li class="breadcrumb-item active">Просмотр файла</li>
    </ol>

    Размер файла: {{ formatSize($file->getSize()) }}<hr>

    @if ($content)
        <pre class="prettyprint linenums">{{ $content }}</pre><br>
    @else
        {!! showError('Данный файл пустой!') !!}
    @endif
@stop
