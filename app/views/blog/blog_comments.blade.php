@extends('layout')

@section('title')
    {{ $blog['title'] }} - Комментарии - @parent
@stop

@section('content')
    <h1><a href="/article/<?=$blog['id']?>"><?=$blog['title']?></a></h1>

    <a href="/article/<?=$blog['id']?>/rss">RSS-лента</a><hr>

    @if ($comments)
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">
                    <div class="img"><?=user_avatars($data['user'])?></div>

                    <div class="pull-right">
                        @if (App::getUserId() != $data['user_id'])
                            <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <noindex>
                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ Blog::class }}" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                            </noindex>
                        @endif

                        @if (App::getUserId() == $data->getUser()->id && $data['created_at'] + 600 > SITETIME)
                            <a href="/article/<?=$blog['id']?>/<?=$data['id']?>/edit?page={{ $page['current'] }}"><i class="fa fa-pencil text-muted"></i></a>
                        @endif
                    </div>

                    <b><?=profile($data['user'])?></b> <small>(<?=date_fixed($data['created_at'])?>)</small><br>
                    <?=user_title($data['user'])?> <?=user_online($data['user'])?>
                </div>
                <div class="message">
                    {!! App::bbCode($data['text']) !!}<br>
                </div>

                @if (is_admin())
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                @endif
            </div>
        @endforeach
        {{ App::pagination($page) }}
    @else
        {{ show_error('Нет сообщений') }}
    @endif

    @if (is_user())
        <div class="form">
            <form action="/article/{{ $blog['id'] }}/comments" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                <textarea id="markItUp" cols="25" rows="5" name="msg"></textarea><br>
                <button class="btn btn-success">Написать</button>
            </form>
        </div><br>

        <a href="/rules">Правила</a> /
        <a href="/smiles">Смайлы</a> /
        <a href="/tags">Теги</a><br><br>

    @else
        {{ show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо') }}
    @endif

<?php
App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам', 'icon' => 'fa-arrow-circle-up']);
App::view('includes/back', ['link' => '/article/'.$blog['id'], 'title' => 'Вернуться']);
?>
@stop
