@extends('layout')

@section('title')
    Редактирование сообщения
@stop

@section('content')

    <h1>Редактирование сообщения</h1>

    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="form">
        <form action="/admin/book/edit/{{ $post->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('guest.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="{{ trans('guest.message_text') }}" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">{{ trans('guest.edit') }}</button>
        </form>
    </div><br>

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/book?page={{ $page }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
