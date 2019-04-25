@extends('layout')

@section('title')
    {{ trans('index.backup') }}
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/admin/backups/create">{{ trans('admin.backup.create_backup') }}</a><br>
        </div><br>
    @endif

    <h1>{{ trans('index.backup') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.backup') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($files)
        @foreach($files as $file)
            <i class="fa fa-archive"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }})
            (<a href="/admin/backups/delete?file={{ basename($file) }}&amp;token={{ $_SESSION['token'] }}">{{ trans('main.delete') }}</a>)<br>
        @endforeach

        <br>{{ trans('admin.backup.total_backups') }}: <b>{{ count($files) }}</b><br><br>
    @else
        {!! showError(trans('admin.backup.empty_backups')) !!}
    @endif
@stop
