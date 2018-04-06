@extends('layout')

@section('title')
    Очистка кэша
@stop

@section('content')

    <h1>Очистка кэша</h1>

    <i class="fa fa-eraser fa-2x"></i> <a href="/admin/cache">Файлы</a> / <b>Изображения</b><br><br>

    @if ($images)
        @foreach ($images as $image)

            <i class="fa fa-image"></i> <b>{{ basename($image) }}</b> ({{ formatFileSize($image) }} / {{ dateFixed(filemtime($image)) }})<br>
        @endforeach

        {!! pagination($page) !!}

        <div class="float-right">
            <form action="/admin/cache/clear" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <input type="hidden" name="type" value="image">
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> Очистить кэш</button>
            </form>
        </div>

        Всего изображений: {{ $page->total }}<br><br>
    @else
        {!! showError('Изображений еще нет!') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
