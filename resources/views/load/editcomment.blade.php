@extends('layout')

@section('title')
    Редактирование комментария
@stop

@section('content')
    <h1>Редактирование комментария</h1>

    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->login }}</b> <small>({{ dateFixed($comment->created_at) }})</small><br><br>

    <div class="form">
        <form action="/down/edit/{{ $comment->relate_id }}/{{ $comment->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ getInput('msg', $comment->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/down/comments/{{ $comment->relate_id }}?page={{ $page }}">Вернуться</a><br>
    <i class="fa fa-arrow-circle-up"></i> <a href="/load">Категории</a><br>
@stop
