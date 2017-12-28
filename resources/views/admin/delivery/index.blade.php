@extends('layout')

@section('title')
    Рассылка приватных сообщений
@stop

@section('content')

    <h1>Рассылка приватных сообщений</h1>

    <div class="form">
        <form action="/admin/delivery" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="markItUp">Текст сообщения:</label>
                <textarea rows="5" class="form-control" id="markItUp" name="msg" required>{{ getInput('msg') }}</textarea>
                {!! textError('msg') !!}
            </div>

            Отправить:<br>

            <label><input name="rec" type="radio" value="1" checked> В онлайне</label><br>
            <label><input name="rec" type="radio" value="1"> Активным</label><br>
            <label><input name="rec" type="radio" value="1"> Администрации</label><br>
            <label><input name="rec" type="radio" value="1"> Всем пользователям</label><br>

            <button class="btn btn-primary">Разослать</button>
        </form>
    </div>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
