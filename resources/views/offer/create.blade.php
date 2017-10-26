@extends('layout')

@section('title')
    Добавление записи
@stop

@section('content')

    <h1>Добавление записи</h1>

    @if (getUser('point') >= setting('addofferspoint'))
        <div class="form">
            <form action="/offers/create" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                Я хотел бы...<br>
                <select name="type">
                    <option value="offer">Предложить идею</option>
                    <option value="issue">Сообщить о проблеме</option>
                </select><br>

                Заголовок: <br>
                <input type="text" name="title" maxlength="50"><br>
                Описание:<br>
                <textarea id="markItUp" cols="25" rows="5" name="text"></textarea><br>
                <button class="btn btn-primary">Добавить</button>
            </form>
        </div><br>

    @else
        {{ showError('Ошибка! Для добавления предложения или проблемы вам необходимо набрать '.plural(setting('addofferspoint'), setting('scorename')).'!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/offers">Вернуться</a><br>
@stop
