@extends('layout')

@section('title')
    Игнор-лист
@stop

@section('content')

    <h1>Игнор-лист</h1>

    @if ($ignores->isNotEmpty())

        <form action="/ignore/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($ignores as $data)
                <div class="b">
                    <div class="img">{!! userAvatar($data->ignoring) !!}</div>

                    <b>{!! profile($data->ignoring) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                    {!! userStatus($data->ignoring) !!} {!! userOnline($data->ignoring) !!}
                </div>

                <div>
                    @if ($data->text)
                        Заметка: {!! bbCode($data->text) !!}<br>
                    @endif

                    <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    <a href="/private/send?user={{ $data->ignoring->login }}">Написать</a> |
                    <a href="/ignore/note/{{ $data->id }}">Заметка</a>
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </form>

        {!! pagination($page) !!}

        Всего в игноре: <b>{{ $page['total'] }}</b><br>
    @else
        {!! showError('Игнор-лист пуст!') !!}
    @endif

    <br>
    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <b>Логин:</b><br>
            <input name="user">
            <input value="Добавить" type="submit">
        </form>
    </div><br>

    <i class="fa fa-users"></i> <a href="/contact">Контакт-лист</a><br>
    <i class="fa fa-envelope"></i> <a href="/private">Сообщения</a><br>
@stop
