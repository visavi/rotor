@extends('layout')

@section('title')
    Админ-логи
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Админ-логи</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($logs)
        @foreach ($logs as $log)
            <div class="b">
                <i class="fa fa-file"></i> <b>{!! $log->user->getProfile() !!}</b>
                 ({{  dateFixed($log->created_at) }})
            </div>
            <div>
                Страница: {{ $log->request }}<br>
                Откуда: {{ $log->referer }}<br>
                <small><span class="data">({{ $log->brow }}, {{ $log->ip }})</span></small>
            </div>
        @endforeach

        {!! pagination($page) !!}

        <i class="fa fa-times"></i> <a href="/admin/logs/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите очистить логи?')">Очистить логи</a><br>

    @else
        {!! showError('Логов еще нет!') !!}
    @endif
@stop
