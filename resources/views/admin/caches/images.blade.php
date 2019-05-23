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
    <i class="fa fa-eraser fa-2x"></i> <a href="/admin/caches" class="badge badge-light">{{ trans('admin.caches.files') }}</a> / <a href="/admin/caches?type=image" class="badge badge-success">{{ trans('admin.caches.images') }}</a><br><br>

    @if ($images)
        @foreach ($images as $image)

            <i class="fa fa-image"></i> <b>{{ basename($image) }}</b> ({{ formatFileSize($image) }} / {{ dateFixed(filemtime($image)) }})<br>
        @endforeach

        {!! pagination($page) !!}

        <div class="float-right">
            <form action="/admin/caches/clear" method="post">
                @csrf
                <input type="hidden" name="type" value="image">
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> {{ trans('admin.caches.clear') }}</button>
            </form>
        </div>

        {{ trans('admin.caches.total_images') }}: {{ $page->total }}<br><br>
    @else
        {!! showError(trans('admin.caches.empty_images')) !!}
    @endif
@stop
