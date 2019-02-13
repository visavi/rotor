@extends('layout')

@section('title')
    Изменение бана пользователя {{ $user->login }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/bans">Бан / Разбан</a></li>
            <li class="breadcrumb-item active">Изменение бана пользователя {{ $user->login }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>{!! $user->getGender() !!} {!! $user->getProfile() !!}</h3>

    @if ($user->lastBan->id)
        Последний бан: {{ dateFixed($user->lastBan->created_at) }}<br>
        Забанил: <b>{!! $user->lastBan->sendUser->getProfile() !!}</b><br>
        Срок: {{ formatTime($user->lastBan->term) }}<br>
        Причина: {!! bbCode($user->lastBan->reason) !!}<br>
    @endif

    До окончания бана: {{ formatTime($user->timeban - SITETIME) }}<br>

    <div class="form">
        <form method="post" action="/admin/bans/change?user={{ $user->login }}">
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
        </form>
    </div>
@stop
