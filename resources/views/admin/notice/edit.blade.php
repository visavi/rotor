@extends('layout')

@section('title')
    Редактирование шаблона
@stop

@section('content')

    <h1>Редактирование шаблона</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/notice">Шаблоны писем</a></li>
            <li class="breadcrumb-item active">Редактирование шаблона</li>
        </ol>
    </nav>

    @if ($notice->protect)
        <div class="p-1 bg-warning text-dark">
            <i class="fa fa-exclamation-circle"></i> <b>Вы редактируете системный шаблон</b>
        </div><br>
    @endif

    <span class="badge badge-info">Тип шаблона: {{ $notice->type }}</span><br>

    <div class="form">
        <form action="/admin/notice/edit/{{ $notice->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="{{ getInput('name', $notice->name) }}" required>
                {!! textError('name') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" rows="15" name="text" required>{{ getInput('text', $notice->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <div class="form-check">
                <label class="form-check-label">
                    <input name="protect" class="form-check-input" type="checkbox" value="1"{{ getInput('protect', $notice->protect) ? ' checked' : '' }}>
                    Системный шаблон
                </label>
            </div>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div>
@stop
