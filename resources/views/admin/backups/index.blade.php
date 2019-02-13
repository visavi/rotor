@extends('layout')

@section('title')
    Backup базы данных
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/admin/backups/create">Создать бэкап</a><br>
        </div><br>
    @endif

    <h1>Backup базы данных</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Backup</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($files)
        @foreach($files as $file)
            <i class="fa fa-archive"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }})
            (<a href="/admin/backups/delete?file={{ basename($file) }}&amp;token={{ $_SESSION['token'] }}">Удалить</a>)<br>
        @endforeach

        <br>Всего бэкапов: <b>{{ count($files) }}</b><br><br>
    @else
        {!! showError('Бэкапов еще нет!') !!}
    @endif
@stop
