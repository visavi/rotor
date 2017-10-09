@extends('layout')

@section('title')
    Заметка для {{ $ignore->ignoring->login }}
@stop

@section('content')

    <h1>Заметка для {{ $ignore->ignoring->login }}</h1>

    <div class="form">
        <form method="post" action="/ignore/note/{{ $ignore->id }}">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Заметка:<br>
            <textarea cols="25" rows="5" name="msg" id="markItUp">{{ $ignore->text }}</textarea><br>
            <input value="Редактировать" type="submit">
        </form>
    </div>
    <br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/ignore">Вернуться</a><br>
@stop
