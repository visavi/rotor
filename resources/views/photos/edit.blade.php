@extends('layout')

@section('title')
    {{ trans('photos.edit_photo') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">{{ trans('index.photos') }}</a></li>
            <li class="breadcrumb-item"><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('photos.edit_photo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/photos/edit/{{ $photo->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">{{ trans('photos.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $photo->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('photos.description') }}:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text">{{ getInput('text', $photo->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            @include('app/_upload', ['id' => $photo->id, 'files' => $photo->files, 'type' => App\Models\Photo::class])

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $photo->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ trans('main.close_comments') }}</label>
            </div>

            <button class="btn btn-success">{{ trans('main.edit') }}</button>
        </form>
    </div>
@stop
