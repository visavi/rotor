@extends('layout')

@section('title')
    Очистка кэша
@stop

@section('content')

    <h1>Очистка кэша</h1>

    <i class="fa fa-eraser fa-2x"></i> <b>Файлы</b> / <a href="/admin/cache?type=image">Изображения</a><br><br>

    @if ($files)
        @foreach ($files as $file)

            <i class="fa fa-file-alt"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }} / {{ dateFixed(filemtime($file)) }})<br>
        @endforeach

        <div class="float-right">
            <form action="/admin/cache/clear" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> Очистить кэш</button>
            </form>
        </div>

        <br>Всего файлов: {{ count($files) }}<br><br>

    @else
        {!! showError('Файлов еще нет!') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
