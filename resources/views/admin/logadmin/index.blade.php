@extends('layout')

@section('title')
    Админ-логи
@stop

@section('content')

    <h1>Админ-логи</h1>

    @if ($logs)
        @foreach ($logs as $log)
            <div class="b">
                <i class="fa fa-file"></i> <b>{!! profile($log->user) !!}</b>
                 ({{  dateFixed($log->created_at) }})
            </div>
            <div>
                Страница: {{ $log->request }}<br>
                Откуда: {{ $log->referer }}<br>
                <small><span class="data">({{ $log->brow }}, {{ $log->ip }})</span></small>
            </div>
        @endforeach

        {!! pagination($page) !!}

        <i class="fa fa-times"></i> <a href="/admin/logadmin/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите очистить логи?')">Очистить логи</a><br>

    @else
        {!! showError('Логов еще нет!') !!}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
