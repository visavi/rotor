@extends('layout')

@section('title')
    Просмотр архива {{ $file->relate->title }}
@stop

@section('content')
    <h1>Просмотр архива {{ $file->relate->title }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>

            @if ($file->relate->category->parent->id)
                <li class="breadcrumb-item"><a href="/load/{{ $file->relate->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/load/{{ $file->relate->category->id }}">{{ $file->relate->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/down/{{ $file->relate->id }}">{{ $file->relate->title }}</a></li>
            <li class="breadcrumb-item active">Просмотр архива</li>
        </ol>
    </nav>

    Всего файлов: {{ $page->total }}<hr>

    @if ($documents)
        @foreach ($documents as $key => $document)

            @if ($document->isFolder())
                <i class="fa fa-folder-open"></i>
                <b>Директория {{ rtrim($document->getName(), '/') }}</b><br>
            @else
                <?php $ext = getExtension($document->getName()) ?>

                {!! icons($ext) !!}

                @if (in_array($ext, $viewExt))
                    <a href="/down/zip/{{ $file->id }}/{{ $key }}">{{ $document->getName() }}</a>
                @else
                    {{ $document->getName() }}
                @endif

                ({{ formatSize($document->getSize()) }})<br>
            @endif

        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('В данном архиве нет файлов!') !!}
    @endif
@stop
