@extends('layout')

@section('title')
    Создание шаблона
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/notices">Шаблоны писем</a></li>
            <li class="breadcrumb-item active">Создание шаблона</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/admin/notices/create" method="post">
            @csrf
            <div class="form-group{{ hasError('type') }}">
                <label for="type">Тип (a-z0-9_-):</label>
                <input type="text" class="form-control" id="type" name="type" maxlength="20" value="{{ getInput('type') }}" required>
                <div class="invalid-feedback">{{ textError('type') }}</div>
            </div>

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="{{ getInput('name') }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" rows="15" name="text" required>{{ getInput('text') }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
            </div>

            <div class="form-check">
                <label class="form-check-label">
                    <input name="protect" class="form-check-input" type="checkbox" value="1"{{ getInput('protect') ? ' checked' : '' }}>
                    Системный шаблон
                </label>
            </div>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div>
@stop
