@extends('layout')

@section('title')
    Редактирование комментария - @parent
@stop

@section('content')
    <h1>Редактирование комментария</h1>

    <i class="fa fa-pencil"></i> <b>{{ $comment->getUser()->login }}</b> <small>({{ date_fixed($comment['created_at']) }})</small><br><br>

    <div class="form">
        <form action="/article/{{ $comment['relate_id'] }}/{{ $comment->id }}/edit?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <textarea id="markItUp" cols="25" rows="5" name="msg">{{ $comment['text'] }}</textarea><br>
            <input type="submit" value="Редактировать">
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/article/{{ $comment['relate_id'] }}/comments?page={{ $page }}">Вернуться</a><br>
@stop
