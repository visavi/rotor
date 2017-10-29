@extends('layout')

@section('title')
    Создание голосования
@stop

@section('content')

    <h1>Создание голосования</h1>

    <div class="form">
        <form action="/votes/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('question') }}">

                <label for="inputQuestion">Вопрос:</label>
                <input type="text" name="question" class="form-control" id="inputQuestion" value="{{ getInput('question') }}" maxlength="100">
                {!! textError('question') !!}
            </div>

            <div class="form-group{{ hasError('answer') }}">

                <?php $answers = array_diff((array) getInput('answer'), ['']) ?>

                @for ($i = 0; $i < 10; $i++)
                    <label for="inputAnswer{{ $i }}">Ответ {{ $i + 1 }}</label>
                    <input type="text" name="answer[]" class="form-control" id="inputAnswer{{ $i }}" value="{{ $answers[$i] ?? '' }}" maxlength="50">
                @endfor
                {!! textError('answer') !!}
            </div>

            <button class="btn btn-primary">Создать голосование</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-up"></i> <a href="/votes">К голосованиям</a><br>
@stop
