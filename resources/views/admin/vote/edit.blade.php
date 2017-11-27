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
                <?php $answers = getInput('answers', $getAnswers); ?>
                <?php $answers = array_slice($answers + array_fill(0, 10, ""), 0, 10, true); ?>

                @foreach ($answers as $key => $answer)
                    <label for="inputAnswers{{ $key }}">
                        @if (isset($getAnswers[$key]))
                            <span class="text-danger">Ответ {{ $loop->iteration }}:</span>
                        @else
                            Ответ {{ $loop->iteration }}:
                        @endif
                    </label>
                   <input type="text" name="answers[{{ $key }}]" class="form-control" id="inputAnswers{{ $key }}" value="{{ $answer }}" maxlength="50">
                @endforeach
                {!! textError('answers') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <p class="text-muted font-italic">Поля отмеченные красным цветом обязательны для заполнения!</p>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
