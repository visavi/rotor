@extends('layout')

@section('title')
    Редактирование комментария
@stop

@section('content')
    <h1>Редактирование комментария</h1>

    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>

        @if ($down->category->parent->id)
            <li class="breadcrumb-item"><a href="/load/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
        @endif

        <li class="breadcrumb-item"><a href="/load/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
        <li class="breadcrumb-item"><a href="/down/{{ $down->id }}">{{ $down->title }}</a></li>
        <li class="breadcrumb-item active">Редактирование комментария</li>
    </ol>

    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->login }}</b> <small>({{ dateFixed($comment->created_at) }})</small><br><br>

    <div class="form">
        <form action="/down/edit/{{ $comment->relate_id }}/{{ $comment->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg', $comment->text) }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div><br>
@stop
