@extends('layout')

@section('title', __('index.backup'))

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/backups/create">{{ __('admin.backup.create_backup') }}</a><br>
    </div>

    <h1>{{ __('index.backup') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.backup') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($files)
        @foreach ($files as $file)
            <div class="section mb-3 shadow">
                <i class="fa fa-archive"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }})

                <div class="float-right">
                    <a href="/admin/backups/delete?file={{ basename($file) }}&amp;token={{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                </div>
            </div>
        @endforeach

        {{ __('admin.backup.total_backups') }}: <b>{{ count($files) }}</b><br>
    @else
        {!! showError(__('admin.backup.empty_backups')) !!}
    @endif
@stop
