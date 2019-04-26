@extends('layout')

@section('title')
    Редактирование шаблона
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/notices">Шаблоны писем</a></li>
            <li class="breadcrumb-item active">Редактирование шаблона</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($notice->protect)
        <div class="p-1 bg-warning text-dark">
            <i class="fa fa-exclamation-circle"></i> <b>Вы редактируете системный шаблон</b>
        </div><br>
    @endif

    <span class="badge badge-info">Тип шаблона: {{ $notice->type }}</span><br>

    <div class="form">
        <form action="/admin/notices/edit/{{ $notice->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="{{ getInput('name', $notice->name) }}" required>
                <div class="invalid-feedback">{{ textError('name') }}</div>
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" rows="15" name="text" required>{{ getInput('text', $notice->text) }}</textarea>
                <div class="invalid-feedback">{{ textError('text') }}</div>
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
