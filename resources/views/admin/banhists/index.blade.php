@extends('layout')

@section('title')
    История банов
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">История банов</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($records->isNotEmpty())

    <form action="/admin/banhists/delete?page={{ $page->current }}" method="post">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

        @foreach ($records as $data)
            <div class="b">

                <div class="float-right">
                    <a href="/admin/bans/change?user={{ $data->user->login }}" data-toggle="tooltip" title="Изменить"><i class="fa fa-pencil-alt"></i></a>
                    <a href="/admin/banhists/view?user={{ $data->user->login }}" data-toggle="tooltip" title="История"><i class="fa fa-history"></i></a>
                    <input type="checkbox" name="del[]" value="{{ $data->id }}">
                </div>

                <div class="img">
                    {!! $data->user->getAvatar() !!}
                    {!! $data->user->getOnline() !!}
                </div>

                <b>{!! $data->user->getProfile() !!}</b>

                <small>({{ dateFixed($data->created_at) }})</small><br>
            </div>
            <div>
                @if ($data->type !== 'unban')
                    Причина: {!! bbCode($data->reason) !!}<br>
                    Срок: {{ formatTime($data->term) }}<br>
                @endif

                {!! $data->getType() !!}: {!! $data->sendUser->getProfile() !!}<br>

            </div>
        @endforeach

        <div class="float-right">
            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </div>
    </form>

    {!! pagination($page) !!}

    <div class="form mb-3">
        <form action="/admin/banhists/view" method="get">
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
@stop
