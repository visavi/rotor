@extends('layout')

@section('title')
    {{ $news->title }}
@stop

@section('description', stripString($news->text))

@section('content')

    <h1>{{ $news->title }} <small> ({{ dateFixed($news->created_at) }})</small></h1>

    @if (isAdmin())
        <div class="form">
            <a href="/admin/news?act=edit&amp;id={{ $news->id }}">Редактировать</a> /
            <a href="/admin/news?act=del&amp;del={{ $news->id }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную новость?')">Удалить</a>
        </div>
    @endif

    @if ($news->image)
        <div class="img">
            <a href="/uploads/news/{{ $news->image }}">{!! resizeImage('uploads/news/', $news->image, ['size' => 100, 'alt' => $news->title]) !!}</a></div>
    @endif

    <div>{!! bbCode($news->text) !!}</div>

    <div style="clear:both;">
        Добавлено: {!! profile($news->user) !!}
    </div><br>

    @if ($comments->isNotEmpty())
        <div class="b"><i class="fa fa-comment"></i> <b>Последние комментарии</b></div>

        @foreach ($comments as $comm)
            <div class="b">
                <div class="img">{!! userAvatar($comm->user) !!}</div>

                <b>{!! profile($comm->user) !!}</b>
                <small> ({{ dateFixed($comm->created_at) }})</small><br>
                {!! userStatus($comm->user) !!} {!! userOnline($comm->user) !!}
            </div>

            <div>
                {!! bbCode($comm->text) !!}<br>

                @if (isAdmin())
                 <span class="data">({{ $comm->brow }}, {{ $comm->ip }})</span>
                @endif
            </div>
        @endforeach

        @if ($news->comments > 5)
            <div class="act">
                <b><a href="/news/comments/{{ $news->id }}">Все комментарии</a></b> ({{ $news->comments }})
                <a href="/news/end/{{ $news->id }}">&raquo;</a>
            </div><br>
        @endif
    @endif

    @if (! $news->closed)
        @if ($comments->isEmpty())
            {!! showError('Комментариев еще нет!') !!}
        @endif

        @if (getUser())
            <div class="form">
                <form action="/news/comments/{{ $news->id }}?read=1" method="post">
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
