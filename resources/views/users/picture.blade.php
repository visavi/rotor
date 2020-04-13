@extends('layout')

@section('title')
    {{ __('index.upload_photo') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.upload_photo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="section-form p-2 shadow">
        @if (!empty($user->picture) && file_exists(HOME . '/' . $user->picture))
            {!! resizeImage($user->picture, ['alt' => $user['login'], 'class' => 'img-fluid rounded']) !!}
        @else
            <img class="img-fluid rounded" src="/assets/img/images/photo.png" alt="Photo">
        @endif

        <form action="/pictures" method="post" enctype="multipart/form-data">
            @csrf
            <div class="custom-file{{ hasError('photo') }}">
                <label class="btn btn-sm btn-secondary" for="inputPhoto">
                    <input id="inputPhoto" type="file" name="photo" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                    {{ __('main.attach_image') }}&hellip;
                </label>
                <span class="badge badge-info" id="upload-file-info"></span>
                <div class="invalid-feedback">{{ textError('photo') }}</div>
            </div>
            <button class="btn btn-primary">{{ __('main.add') }}</button>
        </form>
    </div>

    {{ __('main.valid_file_extensions') }}: jpg, jpeg, gif, png<br>
    {{ __('main.max_file_weight') }}: {{ formatSize(setting('filesize')) }}<br>
    {{ __('main.min_image_size') }}: 100px<br>
    {{ __('users.avatar_generation') }}<br>
@stop
