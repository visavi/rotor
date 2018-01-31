@extends('layout')

@section('title')
    История банов
@stop

@section('content')

    <h1>История банов</h1>

    @if ($records->isNotEmpty())

    <form action="/admin/banhist/delete?page={{ $page['current'] }}" method="post">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        @foreach ($records as $data)
            <div class="b">
                <div class="img">{!! userAvatar($data->user) !!}</div>
                <b>{!! profile($data->user) !!}</b> {!! userOnline($data->user) !!}

                <small>({{ dateFixed($data->created_at) }})</small><br>

                <input type="checkbox" name="del[]" value="{{ $data->id }}">

                <a href="/admin/ban/change?user={{ $data->user->login }}">Изменить</a> / <a href="/admin/banhist/view?user={{ $data->user->login }}">Все изменения</a></div>

            <div>
                @if ($data->type !== 'unban')
                    Причина: {!! bbCode($data->reason) !!}<br>
                    Срок: {{ formatTime($data->term) }}<br>
                @endif

                {!! $data->getType() !!}: {!! profile($data->sendUser) !!}<br>

            </div>
        @endforeach

        <button class="btn btn-sm btn-danger">Удалить выбранное</button>
    </form>

    {!! pagination($page) !!}

    <div class="form mb-3">
        <form action="/admin/banhist/view" method="get">
            <b>Поиск по пользователю:</b><br>
            <div class="form-inline">
                <div class="form-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="Логин пользователя" required>
                </div>

                <button class="btn btn-primary">Найти</button>
            </div>
            {!! textError('user') !!}
        </form>
    </div>

    @else
        {!! showError('Истории банов еще нет!') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
