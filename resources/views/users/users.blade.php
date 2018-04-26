@extends('layout')

@section('title')
    Список пользователей (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Список пользователей</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Список пользователей</li>
        </ol>
    </nav>

    @if ($users->isNotEmpty())
        @foreach($users as $key => $data)

            <div class="b">
                <div class="img">{!! userAvatar($data) !!}</div>

                @if ($user == $data->login)
                    {{ ($page->offset + $key + 1) }}. <b>{!! profile($data, '#ff0000') !!}</b>
                @else
                    {{ ($page->offset + $key + 1) }}. <b>{!! profile($data) !!}</b>
                @endif
                ({{ plural($data->point, setting('scorename')) }})<br>
                {!! userStatus($data) !!} {!! userOnline($data) !!}
            </div>

            <div>
                Форум: {{ $data->allforum }} | Гостевая: {{ $data->allguest }} | Коммент: {{ $data->allcomments }}<br>
                Посещений: {{ $data->visits }}<br>
                Деньги: {{ $data->money }}<br>
                Дата регистрации: {{ dateFixed($data->created_at, 'd.m.Y') }}
            </div>
        @endforeach

        {!! pagination($page) !!}

        <div class="form">
            <form action="/users" method="post">
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

    <i class="fa fa-users"></i> <a href="/who">Новички</a><br>
@stop
