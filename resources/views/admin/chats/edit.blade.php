@extends('layout')

@section('title')
    Редактирование сообщения
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/chats">Админ-чат</a></li>
            <li class="breadcrumb-item active">Редактирование</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt text-muted"></i> <b>{!! $post->user->login !!}</b> ({{ dateFixed($post->created_at) }})<br><br>

    <div class="form">
        <form action="/admin/chats/edit/{{ $post->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="Сообщение" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
