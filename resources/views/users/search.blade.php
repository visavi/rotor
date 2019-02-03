@extends('layout')

@section('title')
    Поиск пользователей
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Поиск пользователей</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form method="get" action="/searchusers/search">

            <div class="form-group{{ hasError('find') }}">
                <label for="find">Логин или имя пользователя:</label>
                <input type="text" class="form-control" id="find" name="find" maxlength="50" placeholder="Логин или имя пользователя" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            <button class="btn btn-primary">Поиск</button>
        </form>
    </div><br>

    <a class="badge badge-pill badge-success" href="/searchusers/1">0-9</a>
    <a class="badge badge-pill badge-success" href="/searchusers/a">A</a>
    <a class="badge badge-pill badge-success" href="/searchusers/b">B</a>
    <a class="badge badge-pill badge-success" href="/searchusers/c">C</a>
    <a class="badge badge-pill badge-success" href="/searchusers/d">D</a>
    <a class="badge badge-pill badge-success" href="/searchusers/e">E</a>
    <a class="badge badge-pill badge-success" href="/searchusers/f">F</a>
    <a class="badge badge-pill badge-success" href="/searchusers/g">G</a>
    <a class="badge badge-pill badge-success" href="/searchusers/h">H</a>
    <a class="badge badge-pill badge-success" href="/searchusers/i">I</a>
    <a class="badge badge-pill badge-success" href="/searchusers/j">J</a>
    <a class="badge badge-pill badge-success" href="/searchusers/k">K</a>
    <a class="badge badge-pill badge-success" href="/searchusers/l">L</a>
    <a class="badge badge-pill badge-success" href="/searchusers/m">M</a>
    <a class="badge badge-pill badge-success" href="/searchusers/n">N</a>
    <a class="badge badge-pill badge-success" href="/searchusers/o">O</a>
    <a class="badge badge-pill badge-success" href="/searchusers/p">P</a>
    <a class="badge badge-pill badge-success" href="/searchusers/q">Q</a>
    <a class="badge badge-pill badge-success" href="/searchusers/r">R</a>
    <a class="badge badge-pill badge-success" href="/searchusers/s">S</a>
    <a class="badge badge-pill badge-success" href="/searchusers/t">T</a>
    <a class="badge badge-pill badge-success" href="/searchusers/u">U</a>
    <a class="badge badge-pill badge-success" href="/searchusers/v">V</a>
    <a class="badge badge-pill badge-success" href="/searchusers/w">W</a>
    <a class="badge badge-pill badge-success" href="/searchusers/x">X</a>
    <a class="badge badge-pill badge-success" href="/searchusers/y">Y</a>
    <a class="badge badge-pill badge-success" href="/searchusers/z">Z</a>
    <br><br>

    Если результат поиска ничего не дал, тогда можно поискать по первым символам логина<br>
    В этом случае будет выдан результат похожий на введенный вами запрос<br><br>
@stop
