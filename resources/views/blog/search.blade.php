@extends('layout')

@section('title')
    Поиск в блогах - @parent
@stop

@section('content')

    <h1>Поиск в блогах</h1>

        <div class="form">
        <form action="/blog/search">
            <input type="hidden" name="act" value="search">

            Запрос:<br>
            <input name="find" size="50"><br>

            Искать:<br>
            <input name="where" type="radio" value="0" checked="checked"> В заголовке<br>
            <input name="where" type="radio" value="1"> В тексте<br><br>

            Тип запроса:<br>
            <input name="type" type="radio" value="0" checked="checked"> И<br>
            <input name="type" type="radio" value="1"> Или<br>
            <input name="type" type="radio" value="2"> Полный<br><br>

            <button class="btn btn-primary">Поиск</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-up"></i> <a href="/blog">К блогам</a><br>
@stop
