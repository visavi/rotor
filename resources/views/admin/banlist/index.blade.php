@extends('layout')

@section('title')
    Список забаненых
@stop

@section('content')

    <h1>Список забаненых</h1>

    @if ($users->isNotEmpty())
        @foreach ($users as $user)
            <div class="b">
                {!! $user->getGender() !!} <b>{!! profile($user) !!}</b>

                @if ($user->lastBan->created_at)
                    (Забанен: {{ dateFixed($user->lastBan->created_at) }})
                @endif
            </div>

            <div>
                До окончания бана: {{ formatTime($user->timeban - SITETIME) }}<br>

                @if ($user->lastBan->id)
                    Забанил: <b>{!! profile($user->lastBan->sendUser) !!}</b><br>
                    Причина: {!! bbCode($user->lastBan->reason) !!}<br>
                @endif

                <i class="fa fa-pencil-alt"></i> <a href="/admin/ban/edit?user={{ $user->login }}">Редактировать</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего забанено: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Пользователей еще нет!') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
