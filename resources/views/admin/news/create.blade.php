@extends('layout')

@section('title')
    Создание новости
@stop

@section('content')

    <h1>Создание новости</h1>

    <div class="form cut">
        <form action="/admin/news/create" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title">Заголовок:</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="100" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" rows="10" name="text" required>{{ getInput('text') }}</textarea>
                {!! textError('text') !!}
            </div>

            <label class="btn btn-sm btn-secondary" for="image">
                <input id="image" type="file" name="image" onchange="$('#upload-file-info').html(this.files[0].name);" hidden>
                Прикрепить картинку&hellip;
            </label>
            <span class="badge badge-info" id="upload-file-info"></span>
            {!! textError('image') !!}
            <br>

            <div class="form-check">
                <label class="form-check-label">
                    <input type="hidden" value="0" name="closed">
                    <input name="closed" class="form-check-input" type="checkbox" value="1"{{ getInput('closed') ? ' checked' : '' }}>
                    Закрыть комментарии
                </label>
            </div>

            <div class="form-check">
                <label class="form-check-label">
                    <input type="hidden" value="0" name="top">
                    <input name="top" class="form-check-input" type="checkbox" value="1"{{ getInput('top') ? ' checked' : '' }}>
                    Показывать на главной
                </label>
            </div>

            <button class="btn btn-primary">Создать</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/news">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
