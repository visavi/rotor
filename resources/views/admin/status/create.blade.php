@extends('layout')

@section('title')
    Добавление статуса
@stop

@section('content')

    <h1>Добавление статуса</h1>

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group">
                <label for="inputFrom">От:</label>
                <input type="text" pattern="\d*" maxlength="10" class="form-control" id="inputFrom" name="topoint" placeholder="От" value="{{ getInput('topoint') }}">

                <label for="inputTo">До:</label>
                <input type="text" pattern="\d*" maxlength="10" class="form-control" id="inputTo" name="point" placeholder="До" value="{{ getInput('point') }}">
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="inputName">Статус:</label>
                <input type="text" maxlength="30" class="form-control" id="inputName" name="name" placeholder="Статус" value="{{ getInput('name') }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('color') }}">
                <label for="inputColor">Цвет:</label>
                <input type="text" maxlength="7" class="form-control" id="inputColor" name="color" placeholder="Цвет" value="{{ getInput('color') }}">
                {!! textError('color') !!}
            </div>

            <button class="btn btn-primary">Добавить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/status">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
