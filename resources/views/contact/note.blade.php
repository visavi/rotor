@extends('layout')

@section('title')
    Заметка для {{ $contact->contactor->login }}
@stop

@section('content')

    <h1>Заметка для {{ $contact->contactor->login }}</h1>

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
