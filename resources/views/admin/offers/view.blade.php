@extends('layout')

@section('title')
    {{ $offer->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->type }}">Предложения / Проблемы</a></li>
            <li class="breadcrumb-item active">{{ $offer->title }}</li>
            <li class="breadcrumb-item"><a href="/offers/{{ $offer->id }}">Обзор</a></li>
        </ol>
    </nav>
@stop

@section('content')
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

        Добавлено: {!! $offer->user->getProfile() !!} ({{ dateFixed($offer->created_at) }})<br>

        <div class="js-rating">Рейтинг:
            <span>{!! formatNum($offer->rating) !!}</span><br>
        </div>

        <a href="/offers/comments/{{ $offer->id }}">Комментарии</a> ({{ $offer->count_comments }})
        <a href="/offers/end/{{ $offer->id }}">&raquo;</a><br>

        @if ($offer->closed)
            <span class="text-danger">Комментирование закрыто</span>
        @endif

    </div><br>

    @if ($offer->reply)
        <div class="b"><b>Официальный ответ</b></div>
        <div class="q">
            {!! bbCode($offer->reply) !!}<br>
            {!! $offer->replyUser->getProfile() !!} ({{ dateFixed($offer->updated_at) }})
        </div><br>
    @endif
@stop
