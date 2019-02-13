@extends('layout')

@section('title')
    IP-бан панель
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('common.panel') }}</a></li>
            <li class="breadcrumb-item active">IP-бан панель</li>
        </ol>
    </nav>
@stop

@section('content')
    <a href="/admin/errors?code=666">История автобанов</a><br>

    @if ($logs->isNotEmpty())

        <form action="/admin/ipbans/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($logs as $log)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $log->id }}">
                    <i class="fa fa-file"></i> <b>{{ $log->ip }}</b>
                </div>

                <div>Добавлено:

                    @if ($log->user->id)
                        <b>{!! $log->user->getProfile() !!}</b><br>
                    @else
                        <b>Автоматически</b><br>
                    @endif

                    Время: {{ dateFixed($log->created_at) }}
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </form>

        {!! pagination($page) !!}

        Всего заблокировано: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('В бан-листе пока пусто!') !!}
    @endif

    <div class="form">
        <form action="/admin/ipbans" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-inline">
                <div class="form-group{{ hasError('ip') }}">
                    <input type="text" class="form-control" id="ip" name="ip" maxlength="15" value="{{ getInput('ip') }}" placeholder="IP-адрес" required>
                </div>

                <button class="btn btn-primary">Добавить</button>
            </div>
            {!! textError('ip') !!}
        </form>
    </div><br>

    <p class="text-muted font-italic">
        Примеры банов: 127.0.0.1 без отступов и пробелов<br>
        Или по маске 127.0.0.* , 127.0.*.* , будут забанены все IP совпадающие по начальным цифрам
    </p>

    @if ($logs->isNotEmpty() && isAdmin('boss'))
        <i class="fa fa-times"></i> <a href="/admin/ipbans/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите очистить список IP?')">Очистить список</a><br>
    @endif
@stop
