@extends('layout')

@section('title')
    Редактирование записи
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->type }}">Предложения / Проблемы</a></li>
            <li class="breadcrumb-item active">Редактирование</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="form">
        <form action="/offers/edit/{{ $offer->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('type') }}">
                <label for="types">Тип:</label>

                <?php $inputType = getInput('type', $offer->type); ?>
                <select class="form-control" name="type" id="type">
                    <option value="offer"{{ $inputType === 'offer' ? ' selected' : '' }}>Предложение</option>
                    <option value="issue"{{ $inputType === 'issue' ? ' selected' : '' }}>Проблема</option>
                </select>

                {!! textError('type') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Заголовок:</label>
                <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title', $offer->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Описание:</label>
                <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text', $offer->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
