@extends('layout')

@section('title')
    {{ trans('index.cache_clear') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.cache_clear') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-eraser fa-2x"></i> <a href="/admin/caches" class="badge badge-success">{{ trans('admin.caches.files') }}</a> / <a href="/admin/caches?type=image" class="badge badge-light">{{ trans('admin.caches.images') }}</a><br><br>

    @if ($files)
        @foreach ($files as $file)

            <i class="fa fa-file-alt"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }} / {{ dateFixed(filemtime($file)) }})<br>
        @endforeach

        <div class="float-right">
            <form action="/admin/caches/clear" method="post">
                @csrf
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> {{ trans('admin.caches.clear') }}</button>
            </form>
        </div>

        <br>{{ trans('admin.caches.total_files') }}: {{ count($files) }}<br><br>

    @else
        {!! showError(trans('admin.caches.empty_files')) !!}
    @endif
@stop
