@extends('layout')

@section('title')
    {{ $news['title'] }} - @parent
@stop

@section('description', stripString($news['text']))

@section('content')

    <h1>{{ $news['title'] }} <small> ({{ dateFixed($news['created_at']) }})</small></h1>

    @if (isAdmin())
        <div class="form">
            <a href="/admin/news?act=edit&amp;id={{ $news->id }}">Редактировать</a> /
            <a href="/admin/news?act=del&amp;del={{ $news->id }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную новость?')">Удалить</a>
        </div>
    @endif

    @if ($news['image'])
        <div class="img">
            <a href="/uploads/news/{{ $news['image'] }}">{!! resizeImage('uploads/news/', $news['image'], 75, ['alt' => $news['title']]) !!}</a></div>
    @endif

    <div>{!! bbCode($news['text']) !!}</div>

    <div style="clear:both;">
        Добавлено: {!! profile($news['user']) !!}
    </div><br>

    @if ($comments->isNotEmpty())
        <div class="act">
            <i class="fa fa-comment"></i> <b>Последние комментарии</b>
        </div>

        @foreach ($comments as $comm)
            <div class="b">
                <div class="img">{!! userAvatar($comm['user']) !!}</div>

                <b>{!! profile($comm['user']) !!}</b>
                <small> ({{ dateFixed($comm['created_at']) }})</small><br>
                {!! userStatus($comm['user']) !!} {!! userOnline($comm['user']) !!}
            </div>

            <div>
                {!! bbCode($comm['text']) !!}<br>

                @if (isAdmin())
                 <span class="data">({{ $comm['brow'] }}, {{ $comm['ip'] }})</span>
                @endif
            </div>
        @endforeach

        @if ($news['comments'] > 5)
            <div class="act">
                <b><a href="/news/{{ $news['id'] }}/comments">Все комментарии</a></b> ({{ $news['comments'] }})
                <a href="/news/{{ $news['id'] }}/end">&raquo;</a>
            </div><br>
        @endif
    @endif

    @if (! $news['closed'])
        @if ($comments->isEmpty())
            {{ showError('Комментариев еще нет!') }}
        @endif

        @if (getUser())
            <div class="form">
                <form action="/news/{{ $news->id }}/comments?read=1" method="post">
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
            {{ showError('Для добавления сообщения необходимо авторизоваться') }}
        @endif
    @else
        {{  showError('Комментирование данной новости закрыто!') }}
    @endif


    <i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br>

@stop
