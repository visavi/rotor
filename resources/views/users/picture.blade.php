@extends('layout')

@section('title')
    {{ trans('index.upload_photo') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.upload_photo') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        @if (!empty($user->picture) && file_exists(HOME . '/' . $user->picture))
            {!! resizeImage($user->picture, ['alt' => $user['login'], 'class' => 'img-fluid rounded']) !!}
        @else
            <img class="img-fluid rounded" src="/assets/img/images/photo.jpg" alt="Фото">
        @endif

        <form action="/pictures" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('photo') }}">
                <label class="btn btn-sm btn-secondary" for="inputPhoto">

                    <input id="inputPhoto" type="file" name="photo" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                    {{ trans('main.attach_image') }}&hellip;
                </label>
                <span class="badge badge-info" id="upload-file-info"></span>
            </div>
            <button class="btn btn-primary">{{ trans('main.add') }}</button>
        </form>
    </div><br>

    {{ trans('main.valid_file_extensions') }}: jpg, jpeg, gif, png<br>
    {{ trans('main.max_file_weight') }}: {{ formatSize(setting('filesize')) }}<br>
    {{ trans('main.min_image_size') }}: 100px<br>
    {{ trans('users.avatar_generation') }}<br>
@stop
