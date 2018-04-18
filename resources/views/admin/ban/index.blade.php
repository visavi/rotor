@extends('layout')

@section('title')
    Бан / Разбан
@stop

@section('content')

    <h1>Бан / Разбан</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Бан / Разбан</li>
        </ol>
    </nav>

    <label for="user">Логин пользователя:</label><br>
    <div class="form">
        <form method="get" action="/admin/ban/edit">
            <div class="form-inline">
                <div class="form-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user') }}" placeholder="Логин пользователя" required>
                </div>

                <button class="btn btn-primary">Редактировать</button>
            </div>
            {!! textError('user') !!}
        </form>
    </div>


    <p class="text-muted font-italic">
        Введите логин пользователя который необходимо отредактировать
    </p>
@stop
