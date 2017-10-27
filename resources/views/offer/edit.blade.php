@extends('layout')

@section('title')
    Редактирование записи
@stop

@section('content')

    <h1>Редактирование записи</h1>

    <div class="form">
        <form action="/offers/{{ $offer->id }}/edit" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            Тип:<br>
            <select name="type">
                <option value="offer"{{ $offer->type == 'offer' ? ' selected' : '' }}>Предложение</option>
                <option value="issue"{{ $offer->type == 'issue' ? ' selected' : '' }}>Проблема</option>
            </select><br>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Заголовок:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $offer->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="markItUp">Описание:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="text" required>{{ getInput('text', $offer->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/offers/{{ $offer->id }}">Вернуться</a><br>
@stop
