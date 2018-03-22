@extends('layout')

@section('title')
    Просмотр архива {{ $down->title }}
@stop

@section('content')
    <h1>Просмотр архива {{ $down->title }}</h1>

    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>

        @if ($down->category->parent->id)
            <li class="breadcrumb-item"><a href="/load/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
        @endif

        <li class="breadcrumb-item"><a href="/load/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
        <li class="breadcrumb-item"><a href="/down/{{ $down->id }}">{{ $down->title }}</a></li>
        <li class="breadcrumb-item active">Просмотр архива</li>
    </ol>

    Всего файлов: {{ $page['total'] }}<hr>

    @if ($files)
        @foreach ($files as $key => $file)

            @if ($file->isFolder())
                <i class="fa fa-folder-open"></i>
                <b>Директория {{ rtrim($file->getName(), '/') }}</b><br>
            @else
                <?php $ext = getExtension($file->getName()) ?>

                {!! icons($ext) !!}

                @if (in_array($ext, $viewExt))
                    <a href="/down/zip/{{ $down->id }}/{{ $key }}">{{ $file->getName() }}</a>
                @else
                    {{ $file->getName() }}
                @endif

                ({{ formatSize($file->getSize()) }})<br>
            @endif

        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('В данном архиве нет файлов!') !!}
    @endif
@stop
