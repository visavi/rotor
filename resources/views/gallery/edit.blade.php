@extends('layout')

@section('title')
    Редактирование фотографии
@stop

@section('content')

    <h1>Редактирование фотографии</h1>

    <div class="form">
        <form action="/gallery/{{ $photo->id }}/edit?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Название: <br><input name="title" value="{{ $photo->title }}"><br>
            Подпись к фото: <br><textarea cols="25" rows="5" name="text">{{ $photo->text }}</textarea><br>

            Закрыть комментарии:

            <input name="closed" type="checkbox" value="1"{{ $checked }}><br>

            <button class="btn btn-success">Изменить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album/{{ getUser('login') }}">Альбом</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Галерея</a><br>
@stop
