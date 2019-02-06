@extends('layout')

@section('title')
    Добавление записи
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/offers/offer">Предложения / Проблемы</a></li>
            <li class="breadcrumb-item active">Добавление</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser('point') >= setting('addofferspoint'))
        <div class="form">
            <form action="/offers/create" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                <?php $inputType = getInput('type', $type); ?>

                <div class="form-group{{ hasError('type') }}">
                    <label for="inputType">Я хотел бы...</label>
                    <select class="form-control" id="inputType" name="type">
                        <option value="offer"{{ $inputType === 'offer' ? ' selected' : '' }}>Предложить идею</option>
                        <option value="issue"{{ $inputType === 'issue' ? ' selected' : '' }}>Сообщить о проблеме</option>
                    </select>
                    {!! textError('type') !!}
                </div>

                <div class="form-group{{ hasError('title') }}">
                    <label for="inputTitle">Название:</label>
                    <input type="text" class="form-control" id="inputTitle" name="title" maxlength="50" value="{{ getInput('title') }}" required>
                    {!! textError('title') !!}
                </div>

                <div class="form-group{{ hasError('text') }}">
                    <label for="text">Текст:</label>
                    <textarea class="form-control markItUp" id="text" rows="5" name="text" required>{{ getInput('text') }}</textarea>
                    {!! textError('text') !!}
                </div>

                <button class="btn btn-primary">Добавить</button>
            </form>
        </div><br>

    @else
        {!! showError('Ошибка! Для добавления предложения или проблемы вам необходимо набрать '.plural(setting('addofferspoint'), setting('scorename')).'!') !!}
    @endif
@stop
