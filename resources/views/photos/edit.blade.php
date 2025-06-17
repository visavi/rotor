@extends('layout')

@section('title', __('photos.edit_photo'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.index') }}">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.view', ['id' => $photo->id]) }}">{{ $photo->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('photos.edit_photo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="{{ route('photos.edit', ['id' => $photo->id, 'page' => $page]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('title') }}">
                <label for="inputTitle" class="form-label">{{ __('photos.name') }}:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="{{ setting('photo_title_max') }}" value="{{ getInput('title', $photo->title) }}" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="mb-3{{ hasError('text') }}">
                <label for="text" class="form-label">{{ __('photos.description') }}:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('photo_text_max') }}" id="text" rows="5" name="text">{{ getInput('text', $photo->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            @include('app/_upload_image', ['model' => $photo])

            <div class="form-check">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="form-check-input" value="1" name="closed" id="closed"{{ getInput('closed', $photo->closed) ? ' checked' : '' }}>
                <label class="form-check-label" for="closed">{{ __('main.close_comments') }}</label>
            </div>

            <button class="btn btn-success">{{ __('main.edit') }}</button>
        </form>
    </div>
@stop
