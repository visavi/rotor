@extends('layout')

@section('title')
    {{ $news->title }} - Комментарии (Стр. {{ $page['current']}})
@stop

@section('content')

    <h1><a href="/news/{{ $news->id }}">{{ $news->title }}</a></h1>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b" id="comment_{{ $data->id }}">
                    <div class="img">{!! userAvatar($data->user) !!}</div>

                    @if (getUser())
                        <div class="float-right">
                            @if (getUser('id') != $data->user_id)
                                <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                                <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\News::class }}" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>

                            @endif

                            @if ($data->user_id == getUser('id') && $data->created_at + 600 > SITETIME)
                                <a title="Редактировать" href="/news/edit/{{ $news->id }}/{{ $data->id }}?page={{ $page['current'] }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                            @endif

                            @if (isAdmin())
                                <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ App\Models\News::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times text-muted"></i></a>
                            @endif
                        </div>
                    @endif

                    <b>{!! profile($data->user) !!}</b>
                    <small> ({{ dateFixed($data->created_at) }})</small><br>
                    {!! userStatus($data->user) !!} {!! userOnline($data->user) !!}
                </div>

                <div class="message">
                    {!! bbCode($data->text) !!}<br>
                </div>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}
    @endif

    @if (! $news->closed)

        @if ($comments->isEmpty())
            {!! showError('Комментариев еще нет!') !!}
        @endif

        @if (getUser())
            <div class="form">
                <form action="/news/comments/{{ $news->id }}" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">Сообщение:</label>
                        <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                        {!! textError('msg') !!}
                    </div>

                    <button class="btn btn-success">Написать</button>
                </form>
            </div>
        <br>
        <a href="/rules">Правила</a> /
        <a href="/smiles">Смайлы</a> /
        <a href="/tags">Теги</a><br><br>
        @else
            {!! showError('Для добавления сообщения необходимо авторизоваться') !!}
        @endif
    @else
        {!! showError('Комментирование данной новости закрыто!') !!}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br>
@stop
