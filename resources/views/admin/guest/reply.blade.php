@extends('layout')

@section('title')
    Ответ на сообщение
@stop

@section('content')

    <h1>Ответ на сообщение</h1>

    <div class="alert alert-info">
        <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br>
        <div>Сообщение: {!! bbCode($post->text) !!}</div>
    </div>

    <div class="form">
        <form action="/admin/book/reply/{{ $post->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('reply') }}">
                <label for="reply">Сообщение:</label>
                <textarea class="form-control markItUp" id="reply" rows="5" name="reply" required>{{ getInput('reply', $post->reply) }}</textarea>
                {!! textError('reply') !!}
            </div>

            <button class="btn btn-primary">Ответить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page={{ $page }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
