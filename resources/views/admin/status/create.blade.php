@extends('layout')

@section('title')
    Добавление статуса
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/status">Статусы пользователей</a></li>
            <li class="breadcrumb-item active">Добавление статуса</li>
        </ol>
    </nav>
@stop

@section('content')
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
                <label for="color">Цвет:</label>

                <div class="input-group colorpick">
                    <input class="form-control col-sm-4" id="color" name="color" type="text" maxlength="7" value="{{ getInput('color') }}">
                    <div class="input-group-append">
                        <span class="input-group-text input-group-addon"><i></i></span>
                    </div>
                </div>

                {!! textError('color') !!}
            </div>

            <button class="btn btn-primary">Добавить</button>
        </form>
    </div>
@stop
