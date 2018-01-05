@extends('layout')

@section('title')
    Редактирование смайла
@stop

@section('content')

    <h1>Редактирование смайла</h1>

    <img src="/uploads/smiles/{{ $smile->name }}" alt=""> — <b>{{ $smile->code }}</b><br>

    <div class="form">
        <form action="/admin/smiles/edit/{{ $smile->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('code') }}">
                <label for="code">Код смайла:</label>
                <input type="text" class="form-control" id="code" name="code" maxlength="20" value="{{ getInput('code', $smile->code) }}" required>
                {!! textError('code') !!}
            </div>

            <p class="text-muted font-italic">
                Код смайла должен начинаться со знака двоеточия<br>
            </p>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/smiles?page={{ $page }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
