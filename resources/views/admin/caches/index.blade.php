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

    <div class="mb-3">
        <?php $active = ($type === 'files') ? 'primary' : 'light'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/admin/caches?type=files">{{ __('admin.caches.files') }}</a>

        <?php $active = ($type === 'images') ? 'primary' : 'light'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/admin/caches?type=images">{{ __('admin.caches.images') }}</a>

        <?php $active = ($type === 'views') ? 'primary' : 'light'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/admin/caches?type=views">{{ __('admin.caches.views') }}</a>
    </div>

    <div class="mb-3">
        <span class="badge bg-success">App env: {{ config('app.env') }}</span>
        <span class="badge bg-success">Cache driver: {{ config('cache.default') }}</span>
    </div>
    <hr>

    @if ($files->isNotEmpty())
        <div class="mb-3">
            @foreach ($files as $file)
                <div class="mb-1">
                    <i class="fa fa-file-alt"></i> <b>{{ basename($file) }}</b> ({{ formatFileSize($file) }} / {{ dateFixed(filemtime($file)) }})
                </div>
            @endforeach
        </div>

        {{ $files->links() }}

        {{ __('main.total') }}: {{ $files->total() }}<br>
    @elseif ($type === 'files' && config('cache.default') !== 'file')
        <div class="alert alert-info">
            {{ __('admin.caches.only_file_cache') }}
        </div>
    @else
        {{ showError(__('admin.caches.empty_files')) }}
    @endif

    <div class="float-end">
        <form action="/admin/caches/clear?type={{ $type }}" method="post">
            @csrf
            <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> {{ __('admin.caches.clear') }}</button>
        </form>
    </div>
@stop
