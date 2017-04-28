@extends('layout')

@section('title')
    История - @parent
@stop

@section('content')

    <h1>История</h1>

    <?php

    echo '<i class="fa fa-envelope"></i> <a href="/private">Входящие</a> / ';
    echo '<a href="/private/output">Отправленные</a> / ';
    echo '<a href="/private/trash">Корзина</a><hr />';

?>
    <i class="fa fa-search"></i> <a href="/searchuser">Поиск контактов</a><br />
    <i class="fa fa-envelope"></i> <a href="/private/send">Написать письмо</a><br />
    <i class="fa fa-address-book"></i> <a href="/contact">Контакт</a> / <a href="/ignore">Игнор</a><br />

@stop
