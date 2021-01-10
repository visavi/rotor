@extends('layout')

@section('title', __('photos.edit_photo'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/photos">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item"><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('photos.edit_photo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form mb-3 shadow">
        <form action="/admin/photos/edit/{{ $photo->id }}?page={{ $page }}" method="post">
            @csrf
            <div class="form-group{{ hasError('title') }}">
                <label for="title">{{ __('photos.name') }}:</label>
                <input class="form-control" id="title" name="title" type="text" value="{{ getInput('title', $photo->title) }}" maxlength="50" required>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ __('photos.description') }}:</label>
                <textarea id="text" class="form-control" cols="25" rows="5" name="text">{{ getInput('text', $photo->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="js-images mb-3">
                @if ($photo->files->isNotEmpty())
                    @foreach ($photo->files as $file)
                        <span class="js-image">
                            {!! resizeImage($file->hash, ['width' => 100]) !!}
                            <a href="#" onclick="return deleteImage(this);" data-id="{{ $file->id }}" data-type="{{ $photo->getMorphClass() }}" data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                        </span>
                    @endforeach
                @endif
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $photo->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ __('main.close_comments') }}</label>
            </div>

            <button class="btn btn-primary">{{ __('main.change') }}</button>
        </form>
    </div>
@stop
