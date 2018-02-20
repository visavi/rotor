@extends('layout')

@section('title')
    Редактирование фотографии
@stop

@section('content')

    <h1>Редактирование фотографии</h1>

    <div class="form">
        <form action="/admin/gallery/edit/{{ $photo->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title">Название:</label>
                <input class="form-control" id="title" name="title" type="text" value="{{ getInput('title', $photo->title) }}" maxlength="50" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Название ссылки:</label>
                <textarea id="text" class="form-control" cols="25" rows="5" name="text">{{ getInput('text', $photo->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <label>
                <input name="closed" class="js-bold" type="checkbox" value="1" {{ getInput('closed', $photo->closed) == 1 ? ' checked' : '' }}> Закрыть комментарии
            </label>
            <br/>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/gallery?page={{ $page }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
