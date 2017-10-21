@extends('layout')

@section('title')
    Ошибки / Автобаны
@stop

@section('content')

    <h1>Ошибки / Автобаны</h1>

    @if (empty(setting('errorlog')))
        <span class="text-danger"><b>Внимание! Запись логов выключена в настройках!</b></span><br>
    @endif

    <ol class="breadcrumb">
        @foreach ($list as $key => $value)
            <li class="breadcrumb-item">
                @if ($key == $code)
                    <b>{{ $value }}</b>
                @else
                    <a href="/admin/log?code={{ $key }}">{{ $value }}</a>
                @endif
            </li>
        @endforeach
    </ol>

    @if ($logs->isNotEmpty())

        @foreach ($logs as $data)
            <div class="b">
                <i class="fa fa-file-o"></i>
                <b>{{ $data->request }} </b> ({{ dateFixed($data->created_at) }})
            </div>
            <div>
                Referer: {{ $data->referer ?: 'Не определено' }}<br>
                Пользователь: {!! profile($data->user) !!}<br>
                <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
            </div>
        @endforeach

        {{ pagination($page) }}

        Всего записей: <b>{{ $page['total'] }}</b><br><br>

        @if (isAdmin('boss'))
            <i class="fa fa-trash-o"></i> <a href="/admin/log/clear?token={{ $_SESSION['token'] }}">Очистить</a><br>
        @endif

    @else
        {{ showError('Записей еще нет!') }}
    @endif

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
