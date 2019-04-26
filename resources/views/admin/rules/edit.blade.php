@extends('layout')

@section('title')
    Редактирование правил
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/rules">Правила сайта</a></li>
            <li class="breadcrumb-item active">Редактирование правил</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/rules/edit" method="post">
            @csrf
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" id="msg" rows="25" name="msg" required>{{ getInput('msg', $rules->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('msg') }}</div>
            </div>
            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <b>Внутренние переменные:</b><br>

    %SITENAME% - Название сайта<br><br>
@stop
