@extends('layout')

@section('title')
    Изменение сообщения
@stop

@section('content')

    <h1>Изменение сообщения</h1>

    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="form">
        <form action="/admin/post/edit/{{ $post->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            @if ($post->files->isNotEmpty())
                <i class="fa fa-paperclip"></i> <b>Удаление файлов:</b><br>
                @foreach ($post->files as $file)
                    <input type="checkbox" name="delfile[]" value="{{ $file->id }}">
                    <a href="/uploads/forum/{{ $post->topic_id }}/{{ $file->hash }}" target="_blank">{{ $file->name }}</a> ({{ formatSize($file->size) }})<br>
                @endforeach
                <br>
            @endif

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div>
    <br>

    <i class="fa fa-arrow-circle-up"></i> <a href="/admin/topic/{{ $post->topic_id }}?page={{ $page }}">Вернуться</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Форум</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
