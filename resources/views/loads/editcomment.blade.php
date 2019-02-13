@extends('layout')

@section('title')
    Редактирование комментария
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">Загрузки</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item active">Редактирование комментария</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->login }}</b> <small>({{ dateFixed($comment->created_at) }})</small><br><br>

    <div class="form">
        <form action="/downs/edit/{{ $comment->relate_id }}/{{ $comment->id }}?page={{ $page }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" maxlength="{{ setting('comment_length') }}" data-hint="{{ trans('common.characters_left') }}" id="msg" rows="5" name="msg" required>{{ getInput('msg', $comment->text) }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div><br>
@stop
