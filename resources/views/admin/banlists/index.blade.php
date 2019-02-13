@extends('layout')

@section('title')
    Забаненные
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Забаненные</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        @foreach ($users as $user)
            <div class="b">
                {!! $user->getGender() !!} <b>{!! $user->getProfile() !!}</b>

                @if ($user->lastBan->created_at)
                    (Забанен: {{ dateFixed($user->lastBan->created_at) }})
                @endif
            </div>

            <div>
                До окончания бана: {{ formatTime($user->timeban - SITETIME) }}<br>

                @if ($user->lastBan->id)
                    Забанил: <b>{!! $user->lastBan->sendUser->getProfile() !!}</b><br>
                    Причина: {!! bbCode($user->lastBan->reason) !!}<br>
                @endif

                <i class="fa fa-pencil-alt"></i> <a href="/admin/bans/edit?user={{ $user->login }}">Редактировать</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего забанено: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Пользователей еще нет!') !!}
    @endif
@stop
