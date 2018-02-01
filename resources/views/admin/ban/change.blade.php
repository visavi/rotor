@extends('layout')

@section('title')
    Бан пользователя {{ $user->login }}
@stop

@section('content')

    <h1>Изменение бана пользователя {{ $user->login }}</h1>

    <h3>{!! $user->getGender() !!} {!! profile($user) !!}</h3>

    @if ($user->lastBan->id)
        Последний бан: {{ dateFixed($user->lastBan->created_at) }}<br>
        Забанил: <b>{!! profile($user->lastBan->sendUser) !!}</b><br>
        Срок: {{ formatTime($user->lastBan->term) }}<br>
        Причина: {!! bbCode($user->lastBan->reason) !!}<br>
    @endif

    До окончания бана: {{ formatTime($user->timeban - SITETIME) }}<br>

    <div class="form">
        <form method="post" action="/admin/ban/change?user={{ $user->login }}">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('timeban') }}">
                <label for="timeban">Бан до:</label>
                <input class="form-control" type="datetime-local" name="timeban" id="timeban" value="{{ getInput('timeban', dateFixed($user->timeban, 'Y-m-d\TH:i')) }}" required>
                {!! textError('timeban') !!}
            </div>

            <div class="form-group{{ hasError('reason') }}">
                <label for="reason">Причина бана:</label>
                <textarea class="form-control markItUp" id="reason" rows="5" name="reason" required>{{ getInput('reason', $user->lastBan->reason) }}</textarea>
                {!! textError('reason') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form></div><br>


    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/ban/edit?user={{ $user->login }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
