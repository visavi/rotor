@extends('layout')

@section('title')
    {{ trans('loads.publish_down') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ trans('loads.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('loads.publish_down') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <form action="/downs/create" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        <div class="form-group{{ hasError('category') }}">
            <label for="inputCategory">{{ trans('loads.load') }}</label>

            <select class="form-control" id="inputCategory" name="cid">
                @foreach ($loads as $data)

                    <option value="{{ $data->id }}"{{ ($cid === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>

                    @if ($data->children->isNotEmpty())
                        @foreach($data->children as $datasub)
                            <option value="{{ $datasub->id }}"{{ $cid === $datasub->id && ! $datasub->closed ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                        @endforeach
                    @endif
                @endforeach

            </select>
            {!! textError('category') !!}
        </div>

        <div class="form-group{{ hasError('title') }}">
            <label for="inputTitle">{{ trans('loads.down_title') }}:</label>
            <input class="form-control" id="inputTitle" name="title" value="{{ getInput('title') }}" required>
            {!! textError('title') !!}
        </div>

        <div class="form-group{{ hasError('text') }}">
            <label for="text">{{ trans('loads.down_text') }}:</label>
            <textarea class="form-control markItUp" id="text" rows="10" name="text" required>{{ getInput('text') }}</textarea>
            {!! textError('text') !!}
        </div>

        <label class="btn btn-sm btn-secondary" for="files">
            <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? '{{ trans('main.files') }}: ' + this.files.length : this.files[0].name);" hidden multiple>
            {{ trans('main.attach_files') }}&hellip;
        </label>
        <span class="badge badge-info" id="upload-file-info"></span>
        {!! textError('files') !!}
        <br>

        <p class="text-muted font-italic">
            {{ trans('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
            {{ trans('main.max_file_weight') }}: {{ formatSize(setting('fileupload')) }}<br>
            {{ trans('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('allowextload')) }}<br>
            {{ trans('main.min_image_size') }}: 100px
        </p>

        <button class="btn btn-primary">{{ trans('main.upload') }}</button>
    </form>
@stop
