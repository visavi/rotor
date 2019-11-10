@extends('layout')

@section('title')
    {{ __('index.cache_clear') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.cache_clear') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-eraser fa-2x"></i> <a href="/admin/caches" class="badge badge-success">{{ __('admin.caches.files') }}</a> / <a href="/admin/caches?type=image" class="badge badge-light">{{ __('admin.caches.images') }}</a><br><br>

    <div>
        Cache driver: <span class="badge badge-pill badge-primary">{{ config('CACHE_DRIVER') }}</span>
    </div>

    @if ($files->isNotEmpty())
        @foreach ($files as $file)

            <i class="fa fa-file-alt"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }} / {{ dateFixed(filemtime($file)) }})<br>
        @endforeach

        <div class="float-right">
            <form action="/admin/caches/clear" method="post">
                @csrf
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> {{ __('admin.caches.clear') }}</button>
            </form>
        </div>

        <br>{{ __('main.total') }}: {{ $files->total() }}<br><br>

    @else
        {!! showError(__('admin.caches.empty_files')) !!}
    @endif

    {{ $files->links('app/_paginator') }}
@stop
