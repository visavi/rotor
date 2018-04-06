@extends('layout')

@section('title')
    Список всех комментариев {{ $user->login }} (Стр. {{ $page->current}})
@stop

@section('content')

    <h1>Список всех комментариев {{ $user->login }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/gallery">Галерея</a></li>
            <li class="breadcrumb-item active">Список всех комментариев {{ $user->login }}</li>
        </ol>
    </nav>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">
                    <i class="fa fa-comment"></i> <b><a href="/gallery/comment/{{ $data->relate_id }}/{{ $data->id }}">{{ $data->title }}</a></b>

                    @if (isAdmin())
                        <a href="#" class="float-right" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ Photo::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times"></i></a>
                    @endif
                </div>

                <div>
                    {!! bbCode($data->text) !!}<br>
                    Написал: <b>{!! profile($data->user) !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>

                    @if (isAdmin())
                        <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                    @endif
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}

    @else
        {!! showError('Комментариев еще нет!') !!}
    @endif
@stop
