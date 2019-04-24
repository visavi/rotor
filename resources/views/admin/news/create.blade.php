@extends('layout')

@section('title')
    {{ trans('news.create_title') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/news">{{ trans('news.site_news') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('news.create_title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form cut">
        <form action="/admin/news/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title">{{ trans('main.title') }}:</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="100" value="{{ getInput('title') }}" placeholder="{{ trans('main.title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('main.text') }}:</label>
                <textarea class="form-control markItUp" maxlength="10000" id="text" rows="10" name="text" placeholder="{{ trans('main.text') }}" required>{{ getInput('text') }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('text') !!}
            </div>

            <label class="btn btn-sm btn-secondary" for="image">
                <input id="image" type="file" name="image" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                {{ trans('main.attach_image') }}&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            {!! textError('image') !!}
            <br>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed') ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ trans('main.close_comments') }}</label>
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="top">
                <input type="checkbox" class="custom-control-input" value="1" name="top" id="top"{{ getInput('top') ? ' checked' : '' }}>
                <label class="custom-control-label" for="top">{{ trans('news.show_on_the_homepage') }}</label>
            </div>

            <button class="btn btn-primary">{{ trans('main.create') }}</button>
        </form>
    </div>
@stop
