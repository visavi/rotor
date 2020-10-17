@extends('layout')

@section('title', __('news.create_title'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/news">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item active">{{ __('news.create_title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form cut">
        <form action="/admin/news/create" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="title">{{ __('main.title') }}:</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="100" value="{{ getInput('title') }}" placeholder="{{ __('main.title') }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ __('main.text') }}:</label>
                <textarea class="form-control markItUp" maxlength="10000" id="text" rows="10" name="text" placeholder="{{ __('main.text') }}" required>{{ getInput('text') }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
                <span class="js-textarea-counter"></span>
            </div>

            <div class="custom-file{{ hasError('image') }}">
                <label class="btn btn-sm btn-secondary" for="image">
                    <input id="image" type="file" name="image" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                    {{ __('main.attach_image') }}&hellip;
                </label>
                <span class="badge badge-info" id="upload-file-info"></span>
                <div class="invalid-feedback">{{ textError('image') }}</div>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed') ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ __('main.close_comments') }}</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="top">
                <input type="checkbox" class="custom-control-input" value="1" name="top" id="top"{{ getInput('top') ? ' checked' : '' }}>
                <label class="custom-control-label" for="top">{{ __('news.show_on_the_homepage') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.create') }}</button>
        </form>
    </div>
@stop
