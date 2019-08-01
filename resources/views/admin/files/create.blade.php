@extends('layout')

@section('title')
    {{ trans('admin.files.create_object') }}
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
            <li class="breadcrumb-item active">{{ trans('admin.files.create_object') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 bg-light p-1">
                <form action="/admin/files/create?path={{ $path }}" method="post">
                    @csrf
                    <div class="form-group{{ hasError('dirname') }}">
                        <label for="dirname">{{ trans('admin.files.directory_name') }}:</label>
                        <input type="text" class="form-control" id="dirname" name="dirname" maxlength="30" value="{{ getInput('dirname') }}" required>
                        <div class="invalid-feedback">{{ textError('dirname') }}</div>
                    </div>

                    <button class="btn btn-primary">{{ trans('admin.files.create_directory') }}</button>
                </form>
            </div>

            <div class="col-md-6 bg-light p-1">
                <form action="/admin/files/create?path={{ $path }}" method="post">
                    @csrf
                    <div class="form-group{{ hasError('filename') }}">
                        <label for="filename">{{ trans('admin.files.file_name') }}:</label>
                        <input type="text" class="form-control" id="filename" name="filename" maxlength="30" value="{{ getInput('filename') }}" required>
                        <div class="invalid-feedback">{{ textError('filename') }}</div>
                    </div>

                    <button class="btn btn-primary">{{ trans('admin.files.create_file') }}</button>
                </form>
            </div>
        </div>

        <p class="text-muted font-italic">{{ trans('admin.files.create_hint') }}</p>
    </div>
@stop
