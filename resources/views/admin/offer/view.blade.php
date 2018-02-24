@extends('layout')

@section('title')
    {{ $offer->title }}
@stop

@section('content')

    <h1>{{ $offer->title }}</h1>

    <i class="fa fa-book"></i>
    <a href="/offers/offer">Предложения</a> /
    <a href="/offers/issue">Проблемы</a> /
    <a href="/offers/{{ $offer->id }}">Обзор</a>
    <hr>

    <div class="b">

        <div class="float-right">
            <a href="/admin/offers/reply/{{ $offer->id }}"><i class="fas fa-reply text-muted"></i></a>
            <a href="/admin/offers/edit/{{ $offer->id }}"><i class="fas fa-pencil-alt text-muted"></i></a>
            <a href="/admin/offers/delete?del={{ $offer->id }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы подтверждаете удаление записи?')"><i class="fas fa-times text-muted"></i></a>
        </div>

        {!! $offer->getStatus() !!}
    </div>

    <div>
        {!! bbCode($offer->text) !!}<br><br>

        Добавлено: {!! profile($offer->user) !!} ({{ dateFixed($offer->created_at) }})<br>

        <div class="js-rating">Рейтинг:
            <span>{!! formatNum($offer->rating) !!}</span><br>
        </div>

        <a href="/offers/comments/{{ $offer->id }}">Комментарии</a> ({{ $offer->comments }})
        <a href="/offers/end/{{ $offer->id }}">&raquo;</a><br>

        @if ($offer->closed)
            <span class="text-danger">Комментирование закрыто</span>
        @endif

    </div><br>

    @if ($offer->reply)
        <div class="b"><b>Официальный ответ</b></div>
        <div class="q">
            {!! bbCode($offer->reply) !!}<br>
            {!! profile($offer->replyUser) !!} ({{ dateFixed($offer->updated_at) }})
        </div><br>
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/admin/offers/{{ $offer->type }}">Вернуться</a><br>
    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
