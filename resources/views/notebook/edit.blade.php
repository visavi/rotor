@extends('layout')

@section('title')
    Редактирование блокнота
@stop

@section('content')

    <h1>Редактирование блокнота</h1>

    <div class="form">
        <form action="/notebook/edit" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="markItUp">Запись:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg">{{ getInput('msg', $note->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div><br>

    * Доступ к личной записи не имеет никто кроме вас<br><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/notebook">Вернуться</a><br>
@stop
