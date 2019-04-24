@extends('layout')

@section('title')
    {{ trans('photos.edit_photo') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/photos">{{ trans('photos.title') }}</a></li>
            <li class="breadcrumb-item"><a href="/photos/{{ $photo->id }}">{{ $photo->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('photos.edit_photo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/photos/edit/{{ $photo->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title">{{ trans('photos.name') }}:</label>
                <input class="form-control" id="title" name="title" type="text" value="{{ getInput('title', $photo->title) }}" maxlength="50" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">{{ trans('photos.description') }}:</label>
                <textarea id="text" class="form-control" cols="25" rows="5" name="text">{{ getInput('text', $photo->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <div class="js-images">
                @if ($photo->files->isNotEmpty())
                    @foreach ($photo->files as $file)
                        <span class="js-image">
                            {!! resizeImage($file->hash, ['width' => 100]) !!}
                            <a href="#" onclick="return deleteImage(this);" data-id="{{ $file->id }}" data-type="{{ App\Models\Photo::class }}"  data-token="{{ $_SESSION['token'] }}"><i class="fas fa-times"></i></a>
                        </span>
                    @endforeach
                @endif
            </div>

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $photo->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">{{ trans('main.close_comments') }}</label>
            </div>

            <button class="btn btn-primary">{{ trans('main.change') }}</button>
        </form>
    </div>
@stop
