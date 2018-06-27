@extends('layout')

@section('title')
    Изменение сообщения
@stop

@section('content')
    <h1>Изменение сообщения</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">Форум</a></li>

            @if ($post->topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forums/{{ $post->topic->forum->parent->id }}">{{ $post->topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forums/{{ $post->topic->forum->id }}">{{ $post->topic->forum->title }}</a></li>

            <li class="breadcrumb-item"><a href="/topics/{{ $post->topic->id }}">{{ $post->topic->title }}</a></li>
            <li class="breadcrumb-item active">Изменение сообщения</li>
        </ol>
    </nav>

    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="form">
        <form action="/posts/edit/{{ $post->id }}?page={{ $page }}" method="post">
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
                    <a href="{{ $file->hash }}" target="_blank">{{ $file->name }}</a> ({{ formatSize($file->size) }})<br>
                @endforeach
                <br>
            @endif

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div>
@stop
