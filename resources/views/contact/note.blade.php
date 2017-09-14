@extends('layout')

@section('title')
    Заметка для - @parent
@stop

@section('content')

    <h1>Заметка для </h1>

    <i class="fa fa-pencil"></i>
    Заметка для пользователя <b>{{ $contact->contactor->login }}</b>
    {!! userOnline($contact->contactor) !!}:
    <br><br>

    <div class="form">
        <form method="post" action="/contact/note/{{ $contact->id }}">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Заметка:<br>
            <textarea cols="25" rows="5" name="msg" id="markItUp">{{ $contact['text'] }}</textarea><br>
            <input value="Редактировать" type="submit">
        </form>
    </div>
    <br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/contact">Вернуться</a><br>
@stop
