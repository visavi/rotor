@extends('layout')

@section('title')
    {{ trans('photos.create_photo') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">{{ trans('photos.title') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('photos.create_photo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/photos/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{  $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ trans('photos.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('photos.description') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text">{{ getInput('text') }}</textarea>
                {!! textError('text') !!}
            </div>

            @include('app/_upload', ['files' => $files, 'type' => App\Models\Photo::class])

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed') ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ trans('main.close_comments') }}</label>
            </div>

            <button class="btn btn-success">{{ trans('main.add') }}</button>
        </form>
    </div>
@stop
