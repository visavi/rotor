@extends('layout')

@section('title')
    Редактирование правил
@stop

@section('content')

    <h1>Редактирование правил</h1>

    <div class="form">
        <form action="/admin/rules/edit" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" id="msg" rows="25" name="msg" required>{{ getInput('msg', $rules->text) }}</textarea>
                {!! textError('msg') !!}
            </div>
            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <b>Внутренние переменные:</b><br>

    %SITENAME% - Название сайта<br><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/rules">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
