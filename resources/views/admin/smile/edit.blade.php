@extends('layout')

@section('title')
    Редактирование смайла
@stop

@section('content')

    <h1>Редактирование смайла</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/smiles">Смайлы</a></li>
            <li class="breadcrumb-item active">Редактирование смайла</li>
        </ol>
    </nav>

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
    </div>
@stop
