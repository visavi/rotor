@extends('layout')

@section('title')
    Корзина - @parent
@stop

@section('content')

    <h1>Корзина</h1>
    
    <i class="fa fa-envelope"></i> <a href="/private">Входящие ({{ $page['totalInbox'] }})</a> / 
    <a href="/private/outbox">Отправленные ({{ $page['totalOutbox'] }})</a> / 

    <b>Корзина ({{ $page['total'] }})</b><hr>

    @if ($messages->isNotEmpty())

        @foreach($messages as $data)

            <div class="b">
            <div class="img">{!! user_avatars($data['author']) !!}</div>
            <b>{!! profile($data['author']) !!}</b>  ({{ date_fixed($data['time']) }})<br>
            {{ user_title($data['author']) }} {!! user_online($data['author']) !!}</div>

            <div>{!! App::bbCode($data['text']) !!}<br>

            <a href="/private/send?user={{ $data->getAuthor()->login }}">Ответить</a> / 
            <a href="/contact?act=add&amp;uz={{ $data->getAuthor()->login }}&amp;token={{ $_SESSION['token'] }}">В контакт</a> / 
            <a href="/ignore?act=add&amp;uz={{ $data->getAuthor()->login }}&amp;token={{ $_SESSION['token'] }}">Игнор</a></div>
        @endforeach

        {{ App::pagination($page) }}

        Всего писем: <b>{{ $page['total'] }}</b><br>
        Срок хранения (дней): <b>{{ Setting::get('expiresmail') }}</b><br><br>

        <i class="fa fa-times"></i> <a href="/private/clear?type=trash&amp;token={{ $_SESSION['token'] }}">Очистить ящик</a><br>
    @else
        {{ show_error('Удаленных писем еще нет!') }}
    @endif
    
    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br>
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br>
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br>

@stop
