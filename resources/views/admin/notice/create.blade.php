@extends('layout')

@section('title')
    Создание шаблона
@stop

@section('content')

    <h1>Создание шаблона</h1>

    <div class="form">
        <form action="/admin/notice/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('type') }}">
                <label for="type">Тип (a-z0-9_-):</label>
                <input type="text" class="form-control" id="type" name="type" maxlength="20" value="{{ getInput('type') }}" required>
                {!! textError('type') !!}
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="{{ getInput('name') }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="markItUp">Текст:</label>
                <textarea class="form-control" id="markItUp" rows="15" name="text" required>{{ getInput('text') }}</textarea>
                {!! textError('text') !!}
            </div>

            <div class="form-check">
                <label class="form-check-label">
                    <input name="protect" class="form-check-input" type="checkbox" value="1"{{ getInput('protect') ? ' checked' : '' }}>
                    Системный шаблон
                </label>
            </div>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/notice">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
