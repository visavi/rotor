@extends('layout')

@section('title')
    Редактирование комментария
@stop

@section('content')

    <h1>Редактирование комментария</h1>

    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->login }}</b> <small>({{ dateFixed($comment->created_at) }})</small><br><br>

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg', $comment->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-success">Редактировать</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery/comments/{{ $comment->relate_id }}?page={{ $page }}">Вернуться</a><br>
@stop
