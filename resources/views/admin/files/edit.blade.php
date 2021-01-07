@extends('layout')

@section('title', __('admin.files.file_editing') . ' ' . $path . $file . '.blade.php')

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/files">{{ __('index.page_editor') }}</a></li>
            @if ($path)
                <li class="breadcrumb-item"><a href="/admin/files?path={{ $path }}">{{ $path }}</a></li>
            @endif
            <li class="breadcrumb-item active">{{ __('admin.files.file_editing') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! $writable)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            {{ __('admin.files.writable') }}
        </div>
    @endif

    <div class="section-form mb-3 shadow">
        <form method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ __('main.text') }}:</label>
                <textarea class="form-control markItUpHtml" rows="25" id="msg" name="msg">{{ getInput('msg', $contest) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.edit') }}</button>
        </form>
    </div>

    <p class="text-muted font-italic">{{ __('admin.files.edit_hint') }}</p>
@stop
