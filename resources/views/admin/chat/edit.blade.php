@extends('layout')

@section('title')
    Редактирование сообщения
@stop

@section('content')
    <h1>Редактирование сообщения </h1>

    <i class="fa fa-pencil-alt text-muted"></i> <b>{!! $post->user->login !!}</b> ({{ dateFixed($post->created_at) }})<br><br>

    <div class="form">
        <form action="/admin/chat/edit/{{ $post->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Сообщение" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/chat?page={{ $page }}">Вернуться</a><br>
@stop
