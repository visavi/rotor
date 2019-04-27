@extends('layout')

@section('title')
    {{ trans('stickers.create_sticker') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ trans('stickers.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('stickers.create_sticker') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/stickers/sticker/create" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group{{ hasError('category') }}">
                <label for="inputCategory">{{ trans('stickers.category') }}</label>

                <select class="form-control" id="inputCategory" name="cid">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($cid === $category->id) ? ' selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('category') }}</div>
            </div>

            <div class="form-group{{ hasError('code') }}">
                <label for="code">{{ trans('stickers.sticker_code') }}:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code') }}" required>
                <div class="invalid-feedback">{{ textError('code') }}</div>
            </div>

            <div class="custom-file{{ hasError('sticker') }}">
                <label class="btn btn-sm btn-secondary" for="sticker">
                    <input id="sticker" type="file" name="sticker" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                    {{ trans('main.attach_image') }}&hellip;
                </label>
                <span class="badge badge-info" id="upload-file-info"></span>
                <div class="invalid-feedback">{{ textError('sticker') }}</div>
            </div>

            <button class="btn btn-primary">{{ trans('main.upload') }}</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">
        {{ trans('stickers.valid_sticker_code') }}<br>
        {{ trans('main.valid_file_extensions') }}: jpg, jpeg, gif, png<br>
        {{ trans('main.max_file_weight') }}: {{ formatSize(setting('stickermaxsize')) }}<br>
        {{ trans('main.max_image_size') }}: {{ setting('stickermaxweight') }} px<br><br>
    </p>
@stop
