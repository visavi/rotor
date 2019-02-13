@extends('layout')

@section('title')
    Очистка кэша изображений
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Очистка кэша</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-eraser fa-2x"></i> <a href="/admin/caches">Файлы</a> / <b>Изображения</b><br><br>

    @if ($images)
        @foreach ($images as $image)

            <i class="fa fa-image"></i> <b>{{ basename($image) }}</b> ({{ formatFileSize($image) }} / {{ dateFixed(filemtime($image)) }})<br>
        @endforeach

        {!! pagination($page) !!}

        <div class="float-right">
            <form action="/admin/caches/clear" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <input type="hidden" name="type" value="image">
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> Очистить кэш</button>
            </form>
        </div>

        Всего изображений: {{ $page->total }}<br><br>
    @else
        {!! showError('Изображений еще нет!') !!}
    @endif
@stop
