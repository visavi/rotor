@extends('layout')

@section('title')
    Игнор-лист
@stop

@section('content')

    <h1>Игнор-лист</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/users/{{ getUser('login') }}">{{ getUser('login') }}</a></li>
            <li class="breadcrumb-item active">Игнор-лист</li>
        </ol>
    </nav>

    @if ($ignores->isNotEmpty())

        <form action="/ignores/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach ($ignores as $data)
                <div class="b">

                    <div class="float-right">
                        <a href="/messages/send?user={{ $data->ignoring->login }}" title="Написать"><i class="fa fa-reply text-muted"></i></a>
                        <a href="/ignores/note/{{ $data->id }}" title="Заметка"><i class="fa fa-sticky-note text-muted"></i></a>
                        <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    </div>

                    <div class="img">{!! userAvatar($data->ignoring) !!}</div>

                    <b>{!! profile($data->ignoring) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>
                    {!! userStatus($data->ignoring) !!} {!! userOnline($data->ignoring) !!}
                </div>

                <div>
                    @if ($data->text)
                        Заметка: {!! bbCode($data->text) !!}<br>
                    @endif
                </div>
            @endforeach

            <div class="float-right">
                <button class="btn btn-sm btn-danger">Удалить выбранное</button>
            </div>
        </form>

        {!! pagination($page) !!}

        Всего в игноре: <b>{{ $page->total }}</b><br>
    @else
        {!! showError('Игнор-лист пуст!') !!}
    @endif

    <div class="form my-3">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form-inline">
                <div class="form-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="Логин пользователя" required>
                </div>

                <button class="btn btn-primary">Добавить</button>
            </div>
            {!! textError('user') !!}
        </form>
    </div>

    <i class="fa fa-users"></i> <a href="/contacts">Контакт-лист</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages">Сообщения</a><br>
@stop
