@extends('layout')

@section('title')
    Добавление фотографии - @parent
@stop

@section('content')

    <h1>Добавление фотографии</h1>

    <div class="form">
        <form action="/gallery/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{  $_SESSION['token'] }}">
            Прикрепить фото:<br /><input type="file" name="photo" /><br />
            Название: <br /><input name="title" /><br />
            Подпись к фото: <br /><textarea cols="25" rows="5" name="text"></textarea><br />

            Закрыть комментарии: <input name="closed" type="checkbox" value="1" /><br />

            <button class="btn btn-success">Добавить</button>
        </form>
    </div><br />

    Разрешается добавлять фотки с расширением jpg, jpeg, gif и png<br />
    Весом не более {{ formatsize(Setting::get('filesize')) }} и размером от 100 до {{ Setting::get('fileupfoto') }} px<br /><br />


    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />
@stop
