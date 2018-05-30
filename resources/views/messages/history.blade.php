@extends('layout')

@section('title')
    История переписки
@stop

@section('content')

    <h1>История переписки</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item"><a href="/messages">Приватные сообщения</a></li>
            <li class="breadcrumb-item active">История переписки</li>
        </ol>
    </nav>

    <i class="fa fa-envelope"></i> <a href="/messages">Входящие</a> /
    <a href="/messages/outbox">Отправленные</a>
    <hr>

    @if ($messages->isNotEmpty())

        @foreach ($messages as $data)
            <div class="b">
                <div class="img">
                    {!! userAvatar($data->author) !!}
                    {!! userOnline($data->author) !!}
                </div>

                <b>{!! profile($data->author) !!}</b>
                ({{  dateFixed($data->created_at) }})
            </div>
            <div>{!! bbCode($data->text) !!}</div>
        @endforeach

        {!! pagination($page) !!}

    @else
        {!! showError('История переписки отсутствует!') !!}
    @endif

    <br>
    <div class="form">
        <form action="/messages/send?user={{ $user->login }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <label for="msg">Сообщение:</label>
            <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="Текст сообщения" required></textarea>

            @if (getUser('point') < setting('privatprotect'))
                {!! view('app/_captcha') !!}
            @endif

            <button class="btn btn-primary">Быстрый ответ</button></form></div><br>

    Всего писем: <b>{{ $page->total }}</b><br><br>

    <i class="fa fa-search"></i> <a href="/searchusers">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">Контакт</a> / <a href="/ignores">Игнор</a><br>
@stop
