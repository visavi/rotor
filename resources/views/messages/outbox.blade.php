@extends('layout')

@section('title')
    Отправленные сообщения
@stop

@section('content')

    <h1>Отправленные сообщения</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item active">Отправленные</li>
        </ol>
    </nav>

    <i class="fa fa-envelope"></i> <a href="/messages">Входящие ({{ $page->totalInbox }})</a> /
    <b>Отправленные ({{ $page->total }})</b>
    <hr>

    @if ($messages->isNotEmpty())
        <form action="/messages/delete?type=outbox&amp;page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form">
                <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked"> <b><label for="all">Отметить все</label></b>
            </div>

            @foreach ($messages as $data)
                <div class="b">
                    <div class="img">
                        {!! userAvatar($data->recipient) !!}
                        {!! userOnline($data->recipient) !!}
                    </div>

                    <b>{!! profile($data->recipient) !!}</b>  ({{ dateFixed($data->created_at) }})<br>
                    {!! userStatus($data->recipient) !!}
                </div>

                <div>{!! bbCode($data->text) !!}<br>

                    <input type="checkbox" name="del[]" value="{{ $data->id }}">
                    <a href="/messages/send?user={{ $data->recipient->login }}">Написать еще</a> /
                    <a href="/messages/history?user={{ $data->recipient->login }}">История</a></div>
            @endforeach

            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </form>

        {!! pagination($page) !!}

        Всего писем: <b>{{ $page->total }}</b><br>
        Объем ящика: <b>{{ setting('limitmail') }}</b><br><br>

        <i class="fa fa-times"></i> <a href="/messages/clear?type=outbox&amp;token={{ $_SESSION['token'] }}">Очистить ящик</a><br>
    @else
        {!! showError('Отправленных писем еще нет!') !!}
    @endif

    <i class="fa fa-search"></i> <a href="/searchusers">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/messages/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contacts">Контакт</a> / <a href="/ignores">Игнор</a><br>
@stop
