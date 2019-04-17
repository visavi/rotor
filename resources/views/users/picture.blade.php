@extends('layout')

@section('title')
    Загрузка фотографии
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Загрузка фотографии</li>
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
                    Прикрепить фото&hellip;
                </label>
                <span class="badge badge-info" id="upload-file-info"></span>
            </div>
            <button class="btn btn-primary">{{ trans('main.add') }}</button>
        </form>
    </div><br>

    Разрешается добавлять файлы с расширением jpg, jpeg, gif, png<br>
    Весом не более {{ formatSize(setting('filesize')) }} и размером от 100px<br>
    Аватар генерируется автоматически из вашей фотографии<br>
@stop
