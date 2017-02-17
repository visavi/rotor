@extends('layout')

@section('title')
    Форум - @parent
@stop

@section('content')

    <h1>Создание новой темы</h1>
    <div class="form">
        <form action="/forum/create" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ App::hasError('fid') }}">
                <label for="inputForum">Форум</label>
                <select class="form-control" id="inputForum" name="fid">

                    <?php foreach ($forums as $data): ?>
                        <?php $selected = ($fid == $data['id']) ? ' selected="selected"' : ''; ?>
                        <?php $disabled = ! empty($data['closed']) ? ' disabled="disabled"' : ''; ?>
                        <option value="<?=$data['id']?>"<?=$selected?><?=$disabled?>><?=$data['title']?></option>

                        <?php if (! $data->children->isEmpty()): ?>
                        <?php foreach($data->children as $datasub): ?>
                        <?php $selected = $fid == $datasub['id'] ? ' selected="selected"' : ''; ?>
                        <?php $disabled = ! empty($datasub['closed']) ? ' disabled="disabled"' : ''; ?>
                        <option value="<?=$datasub['id']?>"<?=$selected?><?=$disabled?>>– <?=$datasub['title']?></option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </select>
                {!! App::textError('fid') !!}
            </div>

            <div class="form-group{{ App::hasError('title') }}">
                <label for="inputTitle">Название темы</label>
                <input name="title" type="text" class="form-control" id="inputTitle"  maxlength="50" placeholder="Название темы" value="{{ App::getInput('title') }}" required>
                {!! App::textError('title') !!}
            </div>

            <div class="form-group{{ App::hasError('msg') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ App::getInput('msg') }}</textarea>
                {!! App::textError('msg') !!}
            </div>

            <?php $checkVote = App::getInput('vote') ? true : false; ?>
            <?php $checked = $checkVote ? ' checked="checked"' : ''; ?>
            <?php $display = $checkVote ? '' : ' style="display: none"'; ?>

            <label>
                <input name="vote" onchange="return showVoteForm();" type="checkbox"{!! $checked !!} /> Создать голосование
            </label><br />

            <div class="js-vote-form"{!! $display !!}>
                <div class="form-group{{ App::hasError('question') }}">

                    <label for="inputQuestion">Вопрос:</label>
                    <input type="text" name="question" class="form-control" id="inputQuestion" value="{{ App::getInput('question') }}" maxlength="100" />
                    {!! App::textError('question') !!}
                </div>

                <div class="form-group{{ App::hasError('answer') }}">

                    <?php $answers = array_diff((array)App::getInput('answer'), ['']) ?>

                    @for ($i=0; $i<10; $i++)
                        <label for="inputAnswer{{ $i }}">Ответ {{ $i + 1 }}</label>
                        <input type="text" name="answer[]" class="form-control" id="inputAnswer{{ $i }}" value="{{ isset($answers[$i]) ? $answers[$i] : '' }}" maxlength="50" />
                    @endfor
                    {!! App::textError('answer') !!}
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Создать тему</button>
        </form>
    </div><br />

    Прежде чем создать новую тему необходимо ознакомиться с правилами<br />
    <a href="/rules">Правила сайта</a><br />
    Также убедись что такой темы нет, чтобы не создавать одинаковые, для этого введи ключевое слово в поиске<br />
    <a href="/forum/search">Поиск по форуму</a><br />
    И если после этого вы уверены, что ваша тема будет интересна другим пользователям, то можете ее создать<br /><br />

@stop
