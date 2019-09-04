@extends('layout')

@section('title')
    {{ __('admin.files.file_editing') }} {{ $path . $fileName }}.blade.php
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/files">{{ __('index.pages_editing') }}</a></li>
            @if ($path)
                <li class="breadcrumb-item"><a href="/admin/files?path={{ $path }}">{{ $path }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ __('admin.files.file_editing') }}</li>
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

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">{{ __('admin.files.edit_hint') }}</p>
@stop
