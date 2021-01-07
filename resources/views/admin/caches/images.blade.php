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
    <a href="/admin/caches" class="badge badge-light">{{ __('admin.caches.files') }}</a>
    <a href="/admin/caches?type=image" class="badge badge-success">{{ __('admin.caches.images') }}</a>
    <hr>

    @if ($images->isNotEmpty())
        <div class="mb-3">
            @foreach ($images as $image)
                <div class="mb-1">
                    <i class="fa fa-image"></i> <b>{{ basename($image) }}</b> ({{ formatFileSize($image) }} / {{ dateFixed(filemtime($image)) }})
                </div>
            @endforeach
        </div>

        <div class="float-right mb-3">
            <form action="/admin/caches/clear" method="post">
                @csrf
                <input type="hidden" name="type" value="image">
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> {{ __('admin.caches.clear') }}</button>
            </form>
        </div>

        {{ $images->links() }}

        {{ __('main.total') }}: {{ $images->total() }}<br>
    @else
        {!! showError(__('admin.caches.empty_images')) !!}
    @endif
@stop
