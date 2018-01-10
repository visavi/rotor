@extends('layout')

@section('title')
    Изменение сообщения
@stop

@section('content')
    <h1>Изменение сообщения</h1>

    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="form">
        <form action="/topic/edit/{{ $post->topic_id }}/{{ $post->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            @if ($files->isNotEmpty())
                <i class="fa fa-paperclip"></i> <b>Удаление файлов:</b><br>
                @foreach ($files as $file)
                    <input type="checkbox" name="delfile[]" value="{{ $file->id }}">
                    <a href="/uploads/forum/{{ $post->topic_id }}/{{ $file->hash }}" target="_blank">{{ $file->name }}</a> ({{ formatSize($file->size) }})<br>
                @endforeach
                <br>
            @endif

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div>
    <br>
@stop
