@extends('layout')

@section('title')
    {{ __('loads.publish_down') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.publish_down') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <form action="/downs/create" method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-group{{ hasError('category') }}">
            <label for="inputCategory">{{ __('loads.load') }}:</label>

            <select class="form-control" id="inputCategory" name="cid">
                @foreach ($loads as $data)

                    <option value="{{ $data->id }}"{{ ($cid === $data->id && ! $data->closed) ? ' selected' : '' }}{{ $data->closed ? ' disabled' : '' }}>{{ $data->name }}</option>

                    @if ($data->children->isNotEmpty())
                        @foreach ($data->children as $datasub)
                            <option value="{{ $datasub->id }}"{{ $cid === $datasub->id && ! $datasub->closed ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                        @endforeach
                    @endif
                @endforeach

            </select>
            <div class="invalid-feedback">{{ textError('category') }}</div>
        </div>

        <div class="form-group{{ hasError('title') }}">
            <label for="inputTitle">{{ __('loads.down_title') }}:</label>
            <input class="form-control" id="inputTitle" name="title" value="{{ getInput('title') }}" maxlength="50" required>
            <div class="invalid-feedback">{{ textError('title') }}</div>
        </div>

        <div class="form-group{{ hasError('text') }}">
            <label for="text">{{ __('loads.down_text') }}:</label>
            <textarea class="form-control markItUp" id="text" rows="10" name="text" maxlength="5000" required>{{ getInput('text') }}</textarea>
            <div class="invalid-feedback">{{ textError('text') }}</div>
            <span class="js-textarea-counter"></span>
        </div>

        <div class="custom-file{{ hasError('files') }}">
            <label class="btn btn-sm btn-secondary" for="files">
                <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? '{{ __('main.files') }}: ' + this.files.length : this.files[0].name);" hidden multiple>
                {{ __('main.attach_files') }}&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            <div class="invalid-feedback">{{ textError('files') }}</div>
        </div>

        <p class="text-muted font-italic">
            {{ __('main.max_file_upload') }}: {{ setting('maxfiles') }}<br>
            {{ __('main.max_file_weight') }}: {{ formatSize(setting('fileupload')) }}<br>
            {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('allowextload')) }}<br>
            {{ __('main.min_image_size') }}: 100px
        </p>

        <button class="btn btn-primary">{{ __('main.upload') }}</button>
    </form>
@stop
