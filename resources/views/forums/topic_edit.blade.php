@extends('layout')

@section('title')
    Изменение темы
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>

            <li class="breadcrumb-item"><a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a></li>
            <li class="breadcrumb-item active">Изменение темы</li>
        </ol>
    </nav>

    <h1>Изменение темы</h1>

    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="form">
        <form action="/topics/edit/{{ $topic->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">


            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Название темы</label>
                <input name="title" type="text" class="form-control" id="inputTitle"  maxlength="50" placeholder="Название темы" value="{{ getInput('title', $topic->title) }}" required>
                {!! textError('title') !!}
            </div>

            @if ($post)
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">Сообщение:</label>
                    <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                    {!! textError('msg') !!}
                </div>
            @endif

            @if ($vote)
                <div class="form-group{{ hasError('question') }}">
                    <label for="question"><span class="text-success">Вопрос:</span></label>
                    <input class="form-control" name="question" id="question" maxlength="100" value="{{ getInput('question', $vote->title) }}" required>
                    {!! textError('question') !!}
                </div>

                @if (! $vote->count)
                    <div class="form-group{{ hasError('answers') }}">
                        <?php $answers = getInput('answers', $vote->getAnswers); ?>
                        <?php $answers = array_slice($answers + array_fill(0, 10, ''), 0, 10, true); ?>

                        @foreach ($answers as $key => $answer)
                            <label for="inputAnswers{{ $key }}">
                                @if (isset($vote->getAnswers[$key]))
                                    <span class="text-success">Ответ {{ $loop->iteration }}:</span>
                                @else
                                    Ответ {{ $loop->iteration }}:
                                @endif
                            </label>
                            <input type="text" name="answers[{{ $key }}]" class="form-control" id="inputAnswers{{ $key }}" value="{{ $answer }}" maxlength="50">
                        @endforeach
                        {!! textError('answers') !!}
                    </div>
                @endif
            @endif

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div>
@stop
