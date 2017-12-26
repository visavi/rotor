@extends('layout')

@section('title')
    Backup базы данных
@stop

@section('content')

    <h1>Backup базы данных</h1>

    @if ($files)

        @foreach($files as $file)
            <i class="fa fa-archive"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }})
            (<a href="/admin/backup/delete?file={{ basename($file) }}&amp;token={{ $_SESSION['token'] }}">Удалить</a>)<br>
        @endforeach

        <br>Всего бэкапов: <b>{{ count($files) }}</b><br><br>
    @else
        {!! showError('Бэкапов еще нет!') !!}
    @endif

    <i class="fa fa-check"></i> <a href="/admin/backup/create">Новый бэкап</a><br>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
