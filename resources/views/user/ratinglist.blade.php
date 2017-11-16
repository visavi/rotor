@extends('layout')

@section('title')
    Рейтинг толстосумов (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Рейтинг толстосумов</h1>

    @if ($users->isNotEmpty())
        @foreach($users as $key => $data)
            <div class="b">
                <div class="img">{!! userAvatar($data) !!}</div>

                {{ ($page['offset'] + $key + 1) }}.

                @if ($user == $data->login)
                    <b>{!! profile($data, '#ff0000') !!}</b>
                @else
                    <b>{!! profile($data) !!}</b>
                @endif
                ({{ plural($data->money, setting('moneyname')) }})<br>
                {!! userStatus($data) !!} {!! userOnline($data) !!}
            </div>

            <div>
                Плюсов: {{ $data->posrating }} / Минусов: {{ $data->negrating }}<br>
                Дата регистрации: {{ dateFixed($data->joined, 'j F Y') }}
            </div>
        @endforeach

        {!! pagination($page) !!}

        <div class="form">
            <b>Поиск пользователя:</b><br>
            <form action="/ratinglist" method="post">
                <input type="text" name="user" value="{{ $user }}">
                <input type="submit" value="Искать">
            </form>
        </div>
        <br>

        Всего пользователей: <b>{{ $page['total'] }}</b><br><br>
    @else
        {{ showError('Пользователей еще нет!') }}
    @endif
@stop
