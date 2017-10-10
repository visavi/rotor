@extends('layout')

@section('title')
   {{ $offer->title }}
@stop

@section('content')

    <h1>{{ $offer->title }} <small>(Голосов: {{ $offer->votes }})</small></h1>

    <i class="fa fa-book"></i> <a href="/offers/offer">Предложения</a> /
    <a href="/offers/issue">Проблемы</a>

    @if (isAdmin('admin'))
        / <a href="/admin/offers/{{ $offer->id }}">Управление</a>
    @endif
    <hr>

    <div class="b">
        {!! $offer->getStatus() !!}
    </div>

    @if (in_array($offer->status, ['wait', 'process']) && getUser('id') === $offer->user_id) {
        <div class="right"><a href="/offers/{{ $offer->id }}/edit">Редактировать</a></div>
    @endif

    <div>{!! bbCode($offer->text) !!}<br><br>

    Добавлено: {!! profile($offer->user) !!} ({{ dateFixed($offer->created_at) }})<br>

    @if ($offer->polling)
        <b><a class="btn btn-danger" href="/offers?act=vote&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'"><i class="fa fa-thumbs-down"></i> Передумал</a></b><br>
    @else
        <b><a class="btn btn-success" href="/offers?act=vote&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'"><i class="fa fa-thumbs-up"></i> Согласен</a></b><br>
    @endif

    <a href="/offers{{ $offer->id }}/comments">Комментарии</a> ({{ $offer->comments }})
    <a href="/offers{{ $offer->id }}/end">&raquo;</a></div><br>

    @if ($offer->reply)
        <div class="b"><b>Официальный ответ</b></div>
        <div class="q">
            {!! bbCode($offer->reply) !!}<br>
            {!! profile($offer->replyUser) !!} ({{ dateFixed($offer->updated_at) }})
        </div><br>
    @endif

    <div class="b"><i class="fa fa-comment"></i> <b>Последние комментарии</b></div><br>

    @if ($offer->lastComments->isNotEmpty())

        @foreach ($offer->lastComments as $comment)
            <div class="b">
                <div class="img">{!! userAvatar($comment->user) !!}</div>

                <b>{!! profile($comment->user) !!}</b>
                <small>({{ dateFixed($comment->created_at) }})</small><br>
                {!! userStatus($comment->user) !!} {!! userOnline($comment->user) !!}
            </div>

            <div>{!! bbCode($comment->text) !!}<br>
                @if (isAdmin())
                    <span class="data">({{ $comment->brow }}, {{ $comment->ip }})</span>
                @endif
            </div>
        @endforeach
        <br>
    @else
        {{ showError('Комментариев еще нет!') }}
    @endif

    @if (getUser())
        @if (! $offer->closed)
            <div class="form">
                <form action="/offers?act=addcomm&amp;id={{ $offer->id }}" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                    <b>Комментарий:</b><br>
                    <textarea cols="25" rows="5" name="msg"></textarea><br>
                    <button class="btn btn-primary">Написать</button>
                </form>
            </div>
            <br>
            <a href="/rules">Правила</a> /
            <a href="/smiles">Смайлы</a> /
            <a href="/tags">Теги</a><br><br>
        @else
            {{  showError('Комментирование данного предложения или проблемы закрыто!') }}
        @endif
    @else
        {{ showError('Для добавления сообщения необходимо авторизоваться') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/offers/{{ $offer->type }}">Вернуться</a><br>

@stop
