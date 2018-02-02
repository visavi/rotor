@extends('layout')

@section('title')
    Редактирование голосования
@stop

@section('content')

    <h1>Редактирование голосования</h1>

    <div class="form">
        <form action="/admin/votes/edit/{{ $vote->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('title') }}">
                <label for="title"><span class="text-danger">Вопрос:</span></label>
                <input class="form-control" name="title" id="title" maxlength="100" value="{{ getInput('title', $vote->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('answers') }}">

                <?php $answers = array_diff((array) getInput('answers'), ['']) ?>

                @for ($i = 0; $i < 10; $i++)


                    <?php var_dump($vote->answers[$i]->id);  ?>
                    @if (! isset($vote->answers[$i]->answer))
                        <label for="inputAnswers{{ $i }}">Ответ {{ $i + 1 }}</label>
                        <input type="text" name="answers[{{ $vote->answers[$i]->id }}]" class="form-control" id="inputAnswers{{ $i }}" value="{{ $answers[$i] ?? $vote->answers[$i]->answer }}" maxlength="50">
                    @else
                        <label for="inputAnswers{{ $i }}"><span class="text-danger">Ответ {{ $i + 1 }}</span></label>
                        <input type="text" name="newanswers[]" class="form-control" id="inputAnswers{{ $i }}" value="{{ $answers[$i] ?? '' }}" maxlength="50">
                    @endif
                @endfor
                {!! textError('answers') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">Поля отмеченные красным цветом обязательны для заполнения!</p>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
