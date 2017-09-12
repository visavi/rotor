@extends('layout')

@section('title')
    Создание новой темы - @parent
@stop

@section('content')

    <h1>Создание новой темы</h1>
    <div class="form">
        <form action="/forum/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('fid') }}">
                <label for="inputForum">Форум</label>
                <select class="form-control" id="inputForum" name="fid">

                    @foreach ($forums as $data)
                        <option value="{{ $data['id'] }}"{!! ($fid == $data['id']) ? ' selected="selected"' : '' !!}{!! !empty($data['closed']) ? ' disabled="disabled"' : '' !!}>{{ $data['title'] }}</option>

                        @if (!$data->children->isEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub['id'] }}"{!! $fid == $datasub['id'] ? ' selected="selected"' : '' !!}{!! !empty($datasub['closed']) ? ' disabled="disabled"' : '' !!}>– {{ $datasub['title'] }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('fid') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Название темы</label>
                <input name="title" class="form-control" id="inputTitle" maxlength="50" placeholder="Название темы" value="{{ getInput('title') }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('msg') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                {!! textError('msg') !!}
            </div>

            <?php $checkVote = getInput('vote') ? true : false; ?>
            <?php $checked = $checkVote ? ' checked="checked"' : ''; ?>
            <?php $display = $checkVote ? '' : ' style="display: none"'; ?>

            <label>
                <input name="vote" onchange="return showVoteForm();" type="checkbox"{!! $checked !!}> Создать
                голосование
            </label><br>

            <div class="js-vote-form"{!! $display !!}>
                <div class="form-group{{ hasError('question') }}">

                    <label for="inputQuestion">Вопрос:</label>
                    <input type="text" name="question" class="form-control" id="inputQuestion" value="{{ getInput('question') }}" maxlength="100">
                    {!! textError('question') !!}
                </div>

                <div class="form-group{{ hasError('answer') }}">

                    <?php $answers = array_diff((array) getInput('answer'), ['']) ?>

                    @for ($i=0; $i<10; $i++)
                        <label for="inputAnswer{{ $i }}">Ответ {{ $i + 1 }}</label>
                        <input type="text" name="answer[]" class="form-control" id="inputAnswer{{ $i }}" value="{{ isset($answers[$i]) ? $answers[$i] : '' }}" maxlength="50">
                    @endfor
                    {!! textError('answer') !!}
                </div>
            </div>
            <button class="btn btn-primary">Создать тему</button>
        </form>
    </div><br>

    Прежде чем создать новую тему необходимо ознакомиться с правилами<br>
    <a href="/rules">Правила сайта</a><br>
    Также убедись что такой темы нет, чтобы не создавать одинаковые, для этого введи ключевое слово в поиске<br>
    <a href="/forum/search">Поиск по форуму</a><br>
    И если после этого вы уверены, что ваша тема будет интересна другим пользователям, то можете ее создать<br><br>

@stop
