@extends('layout')

@section('title')
    Контакт-лист - @parent
@stop

@section('content')

    <h1>Контакт-лист</h1>

    @if ($contacts->isNotEmpty())

        <form action="/contact/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($contacts as $contact)
                <div class="b">
                    <div class="img">{!! userAvatar($contact->contactor) !!}</div>

                    <b>{!! profile($contact->contactor) !!}</b> <small>({{ dateFixed($contact['created_at']) }})</small><br>
                    {!! userStatus($contact->contactor) !!} {!! userOnline($contact->contactor) !!}
                </div>
                <div>
                    @if ($contact['text'])
                        Заметка: {!! bbCode($contact['text']) !!}<br>
                    @endif

                    <input type="checkbox" name="del[]" value="{{ $contact['id'] }}">
                    <a href="/private/send?user={{ $contact->contactor->login }}">Написать</a> |
                    <a href="/transfer?uz={{ $contact->contactor->login }}">Перевод</a> |
                    <a href="/contact/note/{{ $contact['id'] }}">Заметка</a>
                </div>
            @endforeach

            <br><input type="submit" value="Удалить выбранное">
        </form>

        {{ pagination($page) }}

        Всего в контактах: <b>{{ $page['total'] }}</b><br>
    @else
        {{ showError('Контакт-лист пуст!') }}
    @endif

    <br>
    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <b>Логин:</b><br><input name="user">
            <input value="Добавить" type="submit">
        </form>
    </div>
    <br>

    <i class="fa fa-ban"></i> <a href="/ignore">Игнор-лист</a><br>
    <i class="fa fa-envelope"></i> <a href="/private">Сообщения</a><br>
@stop
