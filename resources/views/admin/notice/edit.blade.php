@extends('layout')

@section('title')
    Редактирование шаблона
@stop

@section('content')

    <h1>Редактирование шаблона</h1>

    @if ($notice->protect)
        <div class="info">
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
                <label for="markItUp">Текст:</label>
                <textarea class="form-control" id="markItUp" rows="15" name="text" required>{{ getInput('text', $notice->text) }}</textarea>
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
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/notice">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
