@extends('layout')

@section('title')
    {{ trans('admin.files.file_editing') }} {{ $path . $fileName }}.blade.php
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/files">{{ trans('index.pages_editing') }}</a></li>
            @if ($path)
                <li class="breadcrumb-item"><a href="/admin/files?path={{ $path }}">{{ $path }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ trans('admin.files.file_editing') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <textarea class="form-control markItUpHtml" rows="25" name="msg">{{ getInput('msg', $contest) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ trans('main.edit') }}</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">{{ trans('admin.files.edit_hint') }}</p>
@stop
