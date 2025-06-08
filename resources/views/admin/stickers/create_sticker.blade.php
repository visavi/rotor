@extends('layout')

@section('title', __('stickers.create_sticker'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">{{ __('index.stickers') }}</a></li>
            <li class="breadcrumb-item active">{{ __('stickers.create_sticker') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/stickers/sticker/create" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3{{ hasError('category') }}">
                <label for="inputCategory" class="form-label">{{ __('stickers.category') }}</label>

                <select class="form-select" id="inputCategory" name="cid">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"{{ ($cid === $category->id) ? ' selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">{{ textError('category') }}</div>
            </div>

            <div class="mb-3{{ hasError('code') }}">
                <label for="code" class="form-label">{{ __('stickers.sticker_code') }}:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code') }}" required>
                <div class="invalid-feedback">{{ textError('code') }}</div>
            </div>

            <div class="mb-3{{ hasError('sticker') }}">
                <label class="btn btn-sm btn-secondary" for="sticker">
                    <input id="sticker" type="file" name="sticker" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                    {{ __('main.attach_image') }}&hellip;
                </label>
                <span class="badge bg-info" id="upload-file-info"></span>
                <div class="invalid-feedback">{{ textError('sticker') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.upload') }}</button>
        </form>
    </div>

    <p class="text-muted fst-italic">
        {{ __('stickers.valid_sticker_code') }}<br>
        {{ __('main.valid_file_extensions') }}: {{ str_replace(',', ', ', setting('image_extensions')) }}<br>
        {{ __('main.max_file_weight') }}: {{ formatSize(setting('stickermaxsize')) }}<br>
        {{ __('main.max_image_size') }}: {{ setting('stickermaxweight') }} px<br><br>
    </p>
@stop
