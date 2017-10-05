@extends('layout')

@section('title')
    Загрузка фотографии
@stop

@section('content')

    <h1>Загрузка фотографии</h1>

    <div class="form">
        @if (!empty($user['picture']) && file_exists(HOME.'/uploads/photos/'.$user['picture']))
            {!! resizeImage('uploads/photos/', $user['picture'], setting('previewsize'), ['alt' => $user['login'], 'class' => 'img-fluid rounded']) !!}
        @else
            <img class="img-fluid rounded" src="/assets/img/images/photo.jpg" alt="Фото">
        @endif

        <form action="/pictures" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('photo') }}">
                <label class="btn btn-sm btn-secondary" for="inputPhoto">
                    <input id="inputPhoto" type="file" name="photo"  style="display:none;" onchange="$('#upload-file-info').html($(this).val().replace('C:\\fakepath\\', ''));">
                    Прикрепить фото
                </label>
                <span class='label label-info' id="upload-file-info"></span>
            </div>
            <button class="btn btn-primary">Загрузить</button>
        </form>
    </div><br>

    Разрешается добавлять фотки с расширением jpg, jpeg, gif и png<br>
    Весом не более {{ formatSize(setting('filesize')) }} и размером от 100 до {{  setting('fileupfoto') }} px<br>
    Аватар генерируется автоматически из вашей фотографии<br><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/profile">Вернуться</a><br>
@stop
