@extends('layout')

@section('title')
   {{ $offer->title }}
@stop

@section('content')

    <h1>{{ $offer->title }}</h1>

    <i class="fa fa-book"></i>
    <a href="/offers/offer">Предложения</a> /
    <a href="/offers/issue">Проблемы</a>

    @if (isAdmin('admin'))
        / <a href="/admin/offers/{{ $offer->id }}">Управление</a>
    @endif
    <hr>

    <div class="b">
        {!! $offer->getStatus() !!}

        @if (in_array($offer->status, ['wait', 'process']) && getUser('id') === $offer->user_id)
            <div class="float-right">
                <a title="Редактировать" href="/offers/edit/{{ $offer->id }}"><i class="fa fa-pencil-alt text-muted"></i></a>
            </div>
        @endif
    </div>

    <div>
        {!! bbCode($offer->text) !!}<br><br>

        Добавлено: {!! profile($offer->user) !!} ({{ dateFixed($offer->created_at) }})<br>

        <div class="js-rating">Рейтинг:
            @unless (getUser('id') == $offer->user_id)
                <a class="post-rating-down{{ $offer->vote == '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $offer->id }}" data-type="{{ App\Models\Offer::class }}" data-vote="-" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-down"></i></a>
            @endunless
            <span>{!! formatNum($offer->rating) !!}</span>
            @unless (getUser('id') == $offer->user_id)
                <a class="post-rating-up{{ $offer->vote == '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $offer->id }}" data-type="{{ App\Models\Offer::class }}" data-vote="+" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-thumbs-up"></i></a>
            @endunless
        </div>
    </div><br>

    @if ($offer->reply)
        <div class="b"><b>Официальный ответ</b></div>
        <div class="q">
            {!! bbCode($offer->reply) !!}<br>
            {!! profile($offer->replyUser) !!} ({{ dateFixed($offer->updated_at) }})
        </div><br>
    @endif

    <div class="b"><i class="fa fa-comment"></i> <b>Последние комментарии</b></div>

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

        <div class="act">
            <b><a href="/offers/comments/{{ $offer->id }}">Все комментарии</a></b> ({{ $offer->comments }})
            <a href="/offers/end/{{ $offer->id }}">&raquo;</a>
        </div><br>

    @else
        {!! showError('Комментариев еще нет!') !!}
    @endif

    @if (getUser())
        @if (! $offer->closed)
            <div class="form">
                <form action="/offers/comments/{{ $offer->id }}" method="post">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('msg') }}">
                        <label for="msg">Сообщение:</label>
                        <textarea class="form-control markItUp" id="msg" rows="5" name="msg" required>{{ getInput('msg') }}</textarea>
                        {!! textError('msg') !!}
                    </div>

                    <button class="btn btn-primary">Написать</button>
                </form>
            </div>
            <br>
            <a href="/rules">Правила</a> /
            <a href="/smiles">Смайлы</a> /
            <a href="/tags">Теги</a><br><br>
        @else
            {!! showError('Комментирование данного предложения или проблемы закрыто!') !!}
        @endif
    @else
        {!! showError('Для добавления сообщения необходимо авторизоваться') !!}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/offers/{{ $offer->type }}">Вернуться</a><br>

@stop
