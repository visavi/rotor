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
    <i class="fa fa-eraser fa-2x"></i> <a href="/admin/caches" class="badge badge-light">{{ __('admin.caches.files') }}</a> / <a href="/admin/caches?type=image" class="badge badge-success">{{ __('admin.caches.images') }}</a><br><br>

    @if ($images->isNotEmpty())
        @foreach ($images as $image)

            <i class="fa fa-image"></i> <b>{{ basename($image) }}</b> ({{ formatFileSize($image) }} / {{ dateFixed(filemtime($image)) }})<br>
        @endforeach

        <div class="float-right">
            <form action="/admin/caches/clear" method="post">
                @csrf
                <input type="hidden" name="type" value="image">
                <button class="btn btn-sm btn-danger"><i class="fa fa-trash-alt"></i> {{ __('admin.caches.clear') }}</button>
            </form>
        </div>

        <br>{{ __('main.total') }}: {{ $images->total() }}<br><br>
    @else
        {!! showError(__('admin.caches.empty_images')) !!}
    @endif

    {{ $images->links() }}
@stop
