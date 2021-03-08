@extends('layout')

@section('title', __('index.cache_clear'))

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
    <a href="/admin/caches" class="badge badge-success">{{ __('admin.caches.files') }}</a>
    <a href="/admin/caches?type=image" class="badge badge-light">{{ __('admin.caches.images') }}</a>
    <hr>

    <div class="mb-3">
        Cache driver: <span class="badge badge-pill badge-primary">{{ config('CACHE_DRIVER') }}</span>
    </div>

    @if ($files->isNotEmpty())
        <div class="mb-3">
            @foreach ($files as $file)
                <div class="mb-1">
                    <i class="fa fa-file-alt"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }} / {{ dateFixed(filemtime($file)) }})
                </div>
            @endforeach
        </div>

        <div class="float-right">
            <form action="/admin/caches/clear" method="post">
                @csrf
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> {{ __('admin.caches.clear') }}</button>
            </form>
        </div>

        {{ $files->links() }}

        {{ __('main.total') }}: {{ $files->total() }}<br>
    @else
        {{ showError(__('admin.caches.empty_files')) }}
    @endif
@stop
