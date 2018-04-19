@extends('layout')

@section('title')
    Редактирование фотографии
@stop

@section('content')

    <h1>Редактирование фотографии</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/gallery">Галерея</a></li>
            <li class="breadcrumb-item"><a href="/gallery/{{ $photo->id }}">{{ $photo->title }}</a></li>
            <li class="breadcrumb-item active">Редактирование фотографии</li>
        </ol>
    </nav>

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
@stop
