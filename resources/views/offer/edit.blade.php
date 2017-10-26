@extends('layout')

@section('title')
    Редактирование записи
@stop

@section('content')

    <h1>Редактирование записи</h1>

    <div class="form">
        <form action="/offers/{{ $offer->id }}/edit" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Тип:<br>
            <select name="type">
                <option value="offer"{{ $offer->type == 'offer' ? ' selected' : '' }}>Предложение</option>
                <option value="issue"{{ $offer->type == 'issue' ? ' selected' : ''}}>Проблема</option>
            </select><br>
            Заголовок: <br><input type="text" name="title" value="{{ $offer->title }}"><br>
            Описание: <br><textarea id="markItUp" cols="25" rows="5" name="text">{{ $offer->text }}</textarea><br>
            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/offers/{{ $offer->id }}">Вернуться</a><br>
@stop
