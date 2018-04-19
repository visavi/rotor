@extends('layout')

@section('title')
    Управление гостевой
@stop

@section('content')

    <h1>Управление гостевой</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Гостевая книга</li>
            <li class="breadcrumb-item"><a href="/book?page={{ $page->current }}">Обзор</a></li>
        </ol>
    </nav>

    @if ($posts->isNotEmpty())
        <form action="/admin/book/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach($posts as $data)

                <div class="b">
                    <div class="img">{!! userAvatar($data->user) !!}</div>

                    <div class="float-right">
                        <a href="/admin/book/reply/{{ $data->id }}?page={{ $page->current }}"><i class="fa fa-reply text-muted"></i></a>
                        <a href="/admin/book/edit/{{ $data->id }}?page={{ $page->current }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>

                    @if ($data->user_id)
                        <b>{!! profile($data->user) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                        {!! userStatus($data->user) !!} {!! userOnline($data->user) !!}
                    @else
                        <b>{{ setting('guestsuser') }}</b> <small>({{ dateFixed($data->created_at) }})</small>
                    @endif
                </div>

                <div>
                    {!! bbCode($data->text) !!}<br>

                    @if ($data->edit_user_id)
                        <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: {{ $data->editUser->login }} ({{ dateFixed($data->updated_at) }})</small><br>
                    @endif

                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>

                    @if ($data->reply)
                        <br><span style="color:#ff0000">Ответ: {!! bbCode($data->reply) !!}</span>
                    @endif
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего сообщений: <b>{{ $page->total }}</b><br><br>

        @if (isAdmin('boss'))
            <i class="fa fa-times"></i> <a href="/admin/book/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы уверены что хотите удалить все сообщения?')">Очистить</a><br>
        @endif
    @else
        {!! showError('Сообщений еще нет!') !!}
    @endif
@stop
