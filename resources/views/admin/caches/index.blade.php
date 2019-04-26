@extends('layout')

@section('title')
    Очистка кэша файлов
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">Очистка кэша</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-eraser fa-2x"></i> <b>Файлы</b> / <a href="/admin/caches?type=image">Изображения</a><br><br>

    @if ($files)
        @foreach ($files as $file)

            <i class="fa fa-file-alt"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }} / {{ dateFixed(filemtime($file)) }})<br>
        @endforeach

        <div class="float-right">
            <form action="/admin/caches/clear" method="post">
                @csrf
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> Очистить кэш</button>
            </form>
        </div>

        <br>Всего файлов: {{ count($files) }}<br><br>

    @else
        {!! showError('Файлов еще нет!') !!}
    @endif
@stop
