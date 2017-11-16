@extends('layout')

@section('title')
    Просмотр архива {{ $down->title }}
@stop

@section('content')
    <h1>Просмотр архива {{ $down->title }}</h1>

    Всего файлов: {{ $page['total'] }}<hr>

    @if ($files)
        @foreach ($files as $key => $file)

            @if ($file->isFolder())
                <i class="fa fa-folder-open-o"></i>
                <b>Директория {{ rtrim($file->getName(), '/') }}</b><br>
            @else
                <?php $ext = getExtension($file->getName()) ?>

                {!! icons($ext) !!}

                @if (in_array($ext, $viewExt))
                    <a href="/down/{{ $down->id }}/{{ $key }}/zip">{{ $file->getName() }}</a>
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


    <i class="fa fa-arrow-circle-left"></i> <a href="/down/{{ $down->id }}">Вернуться</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>
@stop
