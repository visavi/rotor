@extends('layout')

@section('title')
    Изменение темы
@stop

@section('content')

    <h1>Изменение темы</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forum">Форум</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/forum/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/forum/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>

            <li class="breadcrumb-item"><a href="/topic/{{ $topic->id }}">{{ $topic->title }}</a></li>
            <li class="breadcrumb-item active">Изменение темы</li>
        </ol>
    </nav>

    <i class="fa fa-pencil-alt"></i> <b>{{ $post->user->login }}</b> <small>({{ dateFixed($post->created_at) }})</small><br><br>

    <div class="form">
        <form action="/topic/edit/{{ $topic->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">


            <div class="form-group{{ hasError('title') }}">
                <label for="inputTitle">Название темы</label>
                <input name="title" type="text" class="form-control" id="inputTitle"  maxlength="50" placeholder="Название темы" value="{{ getInput('title', $topic->title) }}" required>
                {!! textError('title') !!}
            </div>

            @if ($post)
                <div class="form-group{{ hasError('msg') }}">
                    <label for="msg">Сообщение:</label>
                    <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg', $post->text) }}</textarea>
                    {!! textError('msg') !!}
                </div>
            @endif

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div>
@stop
