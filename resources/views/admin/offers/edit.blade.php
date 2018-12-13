@extends('layout')

@section('title')
    Редактирование записи
@stop

@section('content')

    <h1>Редактирование записи</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->type }}">Предложения / Проблемы</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->id }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item active">Редактирование записи</li>
        </ol>
    </nav>

    <div class="form">
        <form action="/admin/offers/edit/{{ $offer->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('type') }}">
                <label for="type">Тип:</label>

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

            <div class="custom-control custom-checkbox">
                <input type="hidden" value="0" name="closed">
                <input type="checkbox" class="custom-control-input" value="1" name="closed" id="closed"{{ getInput('closed', $offer->closed) ? ' checked' : '' }}>
                <label class="custom-control-label" for="closed">Закрыть комментарии</label>
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
