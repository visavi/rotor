@extends('layout')

@section('title')
    Рейтинг толстосумов (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Рейтинг толстосумов</h1>

    @if ($users->isNotEmpty())
        @foreach($users as $key => $data)
            <div class="b">
                <div class="img">
                    {!! userAvatar($data) !!}
                    {!! userOnline($data) !!}
                </div>

                {{ ($page->offset + $key + 1) }}.

                @if ($user == $data->login)
                    <b>{!! profile($data, '#ff0000') !!}</b>
                @else
                    <b>{!! profile($data) !!}</b>
                @endif
                ({{ plural($data->money, setting('moneyname')) }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>
                Плюсов: {{ $data->posrating }} / Минусов: {{ $data->negrating }}<br>
                Дата регистрации: {{ dateFixed($data->created_at, 'd.m.Y') }}
            </div>
        @endforeach

        {!! pagination($page) !!}

        <div class="form">
            <form action="/ratinglists" method="post">
                <div class="form-inline">
                    <div class="form-group{{ hasError('user') }}">
                        <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $user) }}" placeholder="Логин пользователя" required>
                    </div>

                    <button class="btn btn-primary">Искать</button>
                </div>
                {!! textError('user') !!}
            </form>
        </div><br>

        Всего пользователей: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Пользователей еще нет!') !!}
    @endif
@stop
