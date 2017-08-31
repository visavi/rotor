@extends('layout')

@section('title')
    {{ $news['title'] }} - Комментарии (Стр. {{ $page['current']}}) - @parent
@stop

@section('content')

    <h1><a href="/news/{{ $news['id'] }}">{{ $news['title'] }}</a></h1>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b" id="comment_{{ $data['id'] }}">
                    <div class="img">{!! user_avatars($data['user']) !!}</div>

                    <div class="float-right">
                        @if (getUserId() != $data['user_id'])
                            <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ News::class }}" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>

                        @endif

                        @if ($data->user_id == getUserId() && $data['created_at'] + 600 > SITETIME)
                            <a title="Редактировать" href="/news/{{ $news->id }}/{{ $data['id'] }}/edit?page={{ $page['current'] }}"><i class="fa fa-pencil text-muted"></i></a>
                        @endif

                        @if (is_admin())
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data['relate_id'] }}" data-id="{{ $data['id'] }}" data-type="{{ News::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove text-muted"></i></a>
                        @endif
                    </div>

                    <b>{!! profile($data['user']) !!}</b>
                    <small> ({{ date_fixed($data['created_at']) }})</small><br>
                    {!! user_title($data['user']) !!} {!! user_online($data['user']) !!}
                </div>

                <div class="message">
                    {!! bbCode($data['text']) !!}<br>

                    @if (is_admin())
                        <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                    @endif
                </div>
            </div>
        @endforeach

        {{ pagination($page) }}
    @endif

    @if (! $news['closed'])

        @if ($comments->isEmpty())
            {{ showError('Комментариев еще нет!') }}
        @endif

        @if (is_user())
            <div class="form">
                <form action="/news/{{ $news->id }}/comments" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                    <textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br>
                    <button class="btn btn-success">Написать</button>
                </form>
            </div>
        <br>
        <a href="/rules">Правила</a> /
        <a href="/smiles">Смайлы</a> /
        <a href="/tags">Теги</a><br><br>
        @else
            {{showError('Для добавления сообщения необходимо авторизоваться') }}
        @endif
    @else
        {{showError('Комментирование данной новости закрыто!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br>
@stop
