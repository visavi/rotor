@extends('layout')

@section('title')
    Список пользователей (Стр. {{ $page['current'] }}) - @parent
@stop

@section('content')

    <h1>Список пользователей</h1>

    @if ($users->isNotEmpty())
        @foreach($users as $key => $data)

            <div class="b">
                <div class="img">{!! userAvatar($data) !!}</div>

                @if ($user == $data->login)
                    {{ ($page['offset'] + $key + 1) }}. <b>{!! profile($data, '#ff0000') !!}</b>
                @else
                    {{ ($page['offset'] + $key + 1) }}. <b>{!! profile($data) !!}</b>
                @endif
                ({{ plural($data->point, setting('scorename')) }})<br>
                {!! userStatus($data) !!} {!! userOnline($data) !!}
            </div>

            <div>
                Форум: {{ $data->allforum }} | Гостевая: {{ $data->allguest }} | Коммент: {{ $data->allcomments }}<br>
                Посещений: {{ $data->visits }}<br>
                Деньги: {{ $data->money }}<br>
                Дата регистрации: {{ dateFixed($data->joined, 'j F Y') }}
            </div>
        @endforeach

        {{ pagination($page) }}

        <div class="form">
            <b>Поиск пользователя:</b><br>
            <form action="/userlist/search" method="post">
                <input type="text" name="user" value="{{ getUser('login') }}">
                <input type="submit" value="Искать">
            </form>
        </div>
        <br>

        Всего пользователей: <b>{{ $page['total'] }}</b><br><br>
    @else
        {{ showError('Пользователей еще нет!') }}
    @endif

    <i class="fa fa-users"></i> <a href="/onlinewho">Новички</a><br>
@stop
