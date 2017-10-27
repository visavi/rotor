@extends('layout')

@section('title')
    Поиск пользователей
@stop

@section('content')

    <h1>Поиск пользователей</h1>

    <div class="form">
        <form method="post" action="/searchuser/search">

            <div class="form-group{{ hasError('find') }}">
                <label for="find">Логин или имя пользователя:</label>
                <input type="text" class="form-control" id="find" name="find" maxlength="50" placeholder="Логин или имя пользователя" value="{{ getInput('find') }}" required>
                {!! textError('find') !!}
            </div>

            <button class="btn btn-primary">Поиск</button>
        </form>
    </div><br>

    <a class="badge badge-pill badge-success" href="/searchuser/1">0-9</a>
    <a class="badge badge-pill badge-success" href="/searchuser/a">A</a>
    <a class="badge badge-pill badge-success" href="/searchuser/b">B</a>
    <a class="badge badge-pill badge-success" href="/searchuser/c">C</a>
    <a class="badge badge-pill badge-success" href="/searchuser/d">D</a>
    <a class="badge badge-pill badge-success" href="/searchuser/e">E</a>
    <a class="badge badge-pill badge-success" href="/searchuser/f">F</a>
    <a class="badge badge-pill badge-success" href="/searchuser/g">G</a>
    <a class="badge badge-pill badge-success" href="/searchuser/h">H</a>
    <a class="badge badge-pill badge-success" href="/searchuser/i">I</a>
    <a class="badge badge-pill badge-success" href="/searchuser/j">J</a>
    <a class="badge badge-pill badge-success" href="/searchuser/k">K</a>
    <a class="badge badge-pill badge-success" href="/searchuser/l">L</a>
    <a class="badge badge-pill badge-success" href="/searchuser/m">M</a>
    <a class="badge badge-pill badge-success" href="/searchuser/n">N</a>
    <a class="badge badge-pill badge-success" href="/searchuser/o">O</a>
    <a class="badge badge-pill badge-success" href="/searchuser/p">P</a>
    <a class="badge badge-pill badge-success" href="/searchuser/q">Q</a>
    <a class="badge badge-pill badge-success" href="/searchuser/r">R</a>
    <a class="badge badge-pill badge-success" href="/searchuser/s">S</a>
    <a class="badge badge-pill badge-success" href="/searchuser/t">T</a>
    <a class="badge badge-pill badge-success" href="/searchuser/u">U</a>
    <a class="badge badge-pill badge-success" href="/searchuser/v">V</a>
    <a class="badge badge-pill badge-success" href="/searchuser/w">W</a>
    <a class="badge badge-pill badge-success" href="/searchuser/x">X</a>
    <a class="badge badge-pill badge-success" href="/searchuser/y">Y</a>
    <a class="badge badge-pill badge-success" href="/searchuser/z">Z</a>
    <br><br>

    Если результат поиска ничего не дал, тогда можно поискать по первым символам логина<br>
    В этом случае будет выдан результат похожий на введенный вами запрос<br><br>
@stop
