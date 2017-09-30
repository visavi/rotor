@extends('layout')

@section('title')
    Редактирование блокнота - @parent
@stop

@section('content')

    <h1>Редактирование блокнота</h1>

    <div class="form">
        <form action="/notebook/edit" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <textarea id="markItUp" cols="25" rows="10" name="msg">{{ $note['text'] }}</textarea><br>
            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div><br>

    * Доступ к личной записи не имеет никто кроме вас<br><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/notebook">Вернуться</a><br>
@stop
