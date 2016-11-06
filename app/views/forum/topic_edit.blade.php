@extends('layout')

@section('title', 'Изменение темы - @parent')

@section('content')

    <h1>Изменение темы</h1>

    <i class="fa fa-pencil"></i> <b><?=nickname($post['user'])?></b> <small>(<?=date_fixed($post['time'])?>)</small><br /><br />

    <div class="form">
        <form action="/topic/<?=$topic['id']?>/edit" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">


            <div class="form-group{{ App::hasError('title') }}">
                <label for="inputTitle">Название темы</label>
                <input name="title" type="text" class="form-control" id="inputTitle"  maxlength="50" placeholder="Название темы" value="{{ App::getInput('title', $topic['title']) }}" required>
                {!! App::textError('title') !!}
            </div>

            @if ($post)
                <div class="form-group{{ App::hasError('msg') }}">
                    <label for="markItUp">Сообщение:</label>
                    <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ App::getInput('msg', $post['text']) }}</textarea>
                    {!! App::textError('msg') !!}
                </div>
            @endif

            <button type="submit" class="btn btn-primary">Редактировать</button>
        </form>
    </div><br />
@stop
