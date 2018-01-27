@extends('layout')

@section('title')
    Редактирование фотографии
@stop

@section('content')

    <h1>Редактирование фотографии</h1>

    <div class="form">
        <form action="/gallery/edit/{{ $photo->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Название:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $photo->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Подпись к фото:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text">{{ getInput('text', $photo->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            Закрыть комментарии:
            <input name="closed" type="checkbox" value="1"{{ $checked }}><br>

            <button class="btn btn-success">Изменить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album/{{ getUser('login') }}">Альбом</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">Галерея</a><br>
@stop
