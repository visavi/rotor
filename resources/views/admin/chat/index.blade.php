@extends('layout')

@section('title')
    Админ-чат
@stop

@section('content')

    <h1>Админ-чат</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Админ-чат</li>
        </ol>
    </nav>

    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a><hr>

    @if ($posts->isNotEmpty())

        @foreach ($posts as $post)
            <div class="post">
                <div class="b">
                    @if (getUser('id') != $post->user_id)
                        <div class="float-right">
                            <a href="#" onclick="return postReply(this)" data-toggle="tooltip" title="Ответить"><i class="fa fa-reply text-muted"></i></a>
                            <a href="#" onclick="return postQuote(this)" data-toggle="tooltip" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>
                        </div>
                    @endif

                    @if (getUser('id') == $post->user_id && $post->created_at + 600 > SITETIME)
                        <div class="float-right">
                            <a href="/admin/chat/edit/{{ $post->id }}?page={{ $page['current'] }}" title="Редактировать"><i class="fas fa-pencil-alt text-muted"></i></a>
                        </div>
                    @endif

                    <div class="img">{!! userAvatar($post->user) !!}</div>

                    <b>{!! profile($post->user) !!}</b> <small>({{ dateFixed($post->created_at) }})</small><br>
                    {!! userStatus($post->user) !!} {!! userOnline($post->user) !!}
                </div>

                <div class="message">{!! bbCode($post->text) !!}</div>

                @if ($post->edit_user_id)
                    <small><i class="fa fa-exclamation-circle"></i> Отредактировано: {!! profile($post->editUser) !!} ({{ dateFixed($post->updated_at) }})</small><br>
                @endif

                <span class="data">({{ $post->brow }}, {{ $post->ip }})</span>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Сообщений нет, будь первым!') !!}
    @endif

    <div class="form">
        <form action="/admin/chat" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">Сообщение:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" name="msg" placeholder="Сообщение:" required>{{ getInput('msg') }}</textarea>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-primary">Написать</button>
        </form>
    </div><br>

    @if (isAdmin('boss') && $page['total'] > 0)
        <i class="fa fa-times"></i> <a href="/admin/chat/clear?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите очистить админ-чат?')">Очистить чат</a><br>
    @endif

@stop
