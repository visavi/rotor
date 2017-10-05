@extends('layout')

@section('title')
    Редактирование сообщения
@stop

@section('content')
    <h1>Редактирование сообщения</h1>

    <i class="fa fa-pencil text-muted"></i> <b>{!! $post->user->login !!}</b> ({{ dateFixed($post->time) }})<br><br>

    <div class="form">
        <form action="/book/edit/{{ $post->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/book">Вернуться</a><br>
@stop
