@extends('layout')

@section('title')
    Редактирование комментария
@stop

@section('content')

    <h1>Редактирование комментария</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/gallery">Галерея</a></li>
            <li class="breadcrumb-item"><a href="/gallery/{{ $photo->id }}">{{ $photo->title }}</a></li>
            <li class="breadcrumb-item"><a href="/gallery/comments/{{ $photo->id }}">Комментариии</a></li>
            <li class="breadcrumb-item active">Редактирование фотографии</li>
        </ol>
    </nav>

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
@stop
