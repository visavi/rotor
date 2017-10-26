@extends('layout')

@section('title')
    Добавление фотографии
@stop

@section('content')

    <h1>Добавление фотографии</h1>

    <div class="form">
        <form action="/gallery/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{  $_SESSION['token'] }}">

            <div class="form-group{{ hasError('photo') }}">
                <label for="inputPhoto">Прикрепить фото:</label>
                <input type="file" class="form-control" id="inputPhoto" name="photo" required>
                {!! textError('photo') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Название:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="markItUp">Подпись к фото:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="text">{{ getInput('text') }}</textarea>
                {!! textError('text') !!}
            </div>

            Закрыть комментарии:
            <input name="closed" type="checkbox" value="1"><br>

            <button class="btn btn-success">Добавить</button>
        </form>
    </div><br>

    Разрешается добавлять фотки с расширением jpg, jpeg, gif и png<br>
    Весом не более {{ formatSize(setting('filesize')) }} и размером от 100 до {{ setting('fileupfoto') }} px<br><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
@stop
