@extends('layout')

@section('title')
    Редактирование блокнота
@stop

@section('content')

    <h1>Редактирование блокнота</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">Мое меню</a></li>
            <li class="breadcrumb-item"><a href="/notebooks">Блокнот</a></li>
            <li class="breadcrumb-item active">Редактирование блокнота</li>
        </ol>
    </nav>

    <div class="form">
        <form action="/notebooks/edit" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Запись:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg">{{ getInput('msg', $note->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div><br>

    * Личная запись доступна только для вас<br><br>
@stop
