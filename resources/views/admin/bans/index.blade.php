@extends('layout')

@section('title')
    Бан / Разбан
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Бан / Разбан</li>
        </ol>
    </nav>
@stop

@section('content')
    <label for="user">Логин пользователя:</label><br>
    <div class="form">
        <form method="get" action="/admin/bans/edit">
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
