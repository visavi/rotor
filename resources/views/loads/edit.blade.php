@extends('layout')

@section('title')
    {{ trans('loads.edit_down') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ trans('index.loads') }}</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('loads.edit_down') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="p-1 bg-warning text-dark">
        <i class="fas fa-exclamation-triangle"></i> {{ trans('loads.pending_down1') }}<br>
        {{ trans('loads.pending_down2') }}
    </div><br>

    <div class="form mb-3">
        <form action="/downs/edit/{{ $down->id }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="title">{{ trans('loads.down_title') }}:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $down->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('loads.down_text') }}:</label>
                <textarea class="form-control markItUp" id="text" name="text" rows="5">{{ getInput('text', $down->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            @if ($down->getFiles()->isNotEmpty())
                @foreach ($down->getFiles() as $file)
                    <i class="fa fa-download"></i>
                    <b><a href="{{ $file->hash }}">{{ $file->name }}</a></b> ({{ formatSize($file->size) }}) (<a href="/downs/delete/{{ $down->id }}/{{ $file->id }}" onclick="return confirm('{{ trans('loads.confirm_delete_file') }}')">{{ trans('main.delete') }}</a>)<br>
                @endforeach
            @endif

            @if ($down->getImages()->isNotEmpty())
                @foreach ($down->getImages() as $image)
                    {!! resizeImage($image->hash) !!}<br>
                    <i class="fa fa-image"></i> <b><a href="{{ $image->hash }}">{{ $image->name }}</a></b> ({{ formatSize($image->size ) }}) (<a href="/downs/delete/{{ $down->id }}/{{ $image->id }}" onclick="return confirm('{{ trans('loads.confirm_delete_screen') }}')">{{ trans('main.delete') }}</a>)<br><br>
                @endforeach
            @endif

            @if ($down->files->count() < setting('maxfiles'))
                <div class="custom-file{{ hasError('files') }}">
                    <label class="btn btn-sm btn-secondary" for="files">
                        <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? '{{ trans('main.files') }}: ' + this.files.length : this.files[0].name);" hidden multiple>
                        {{ trans('main.attach_files') }}&hellip;
                    </label>
                    <span class="badge badge-info" id="upload-file-info"></span>
                    <div class="invalid-feedback">{{ textError('files') }}</div>
                </div>
            @endif

            <p class="text-muted font-italic">
                {{ trans('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
                {{ trans('main.max_file_weight') }}: {{ formatSize(setting('fileupload')) }}<br>
                {{ trans('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('allowextload')) }}<br>
                {{ trans('main.min_image_size') }}: 100px
            </p>

            <button class="btn btn-primary">{{ trans('main.edit') }}</button>
        </form>
    </div>
@stop
