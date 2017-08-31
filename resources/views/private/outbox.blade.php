@extends('layout')

@section('title')
    Отправленные сообщения - @parent
@stop

@section('content')

    <h1>Отправленные сообщения</h1>

    <i class="fa fa-envelope"></i> <a href="/private">Входящие ({{ $page['totalInbox'] }})</a> /
    <b>Отправленные ({{ $page['total'] }})</b> /
    <a href="/private/trash">Корзина ({{  $page['totalTrash'] }})</a><hr>

    @if ($messages->isNotEmpty())
        <form action="/private/delete?type=outbox&amp;page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form">
                <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked"> <b><label for="all">Отметить все</label></b>
            </div>

            @foreach ($messages as $data)
                <div class="b">
                    <div class="img">{!! userAvatar($data['recipient']) !!}</div>
                    <b>{!! profile($data['recipient']) !!}</b>  ({{ dateFixed($data['created_at']) }})<br>
                    {!! userStatus($data['recipient']) !!} {!! user_online($data['recipient']) !!}</div>

                <div>{!! bbCode($data['text']) !!}<br>

                    <input type="checkbox" name="del[]" value="{{ $data['id'] }}">
                    <a href="/private/send?user={{ $data->getRecipient()->login }}">Написать еще</a> /
                    <a href="/private/history?user={{ $data->getRecipient()->login }}">История</a></div>
            @endforeach

            <br><input type="submit" value="Удалить выбранное"></form>

        {{ pagination($page) }}

        Всего писем: <b>{{ $page['total'] }}</b><br>
        Объем ящика: <b>{{ setting('limitmail') }}</b><br><br>

        <i class="fa fa-times"></i> <a href="/private/clear?type=outbox&amp;token={{ $_SESSION['token'] }}">Очистить ящик</a><br>
    @else
        {{ showError('Отправленных писем еще нет!') }}
    @endif

    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br>

@stop
