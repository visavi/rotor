@extends('layout')

@section('title')
    Загрузки - Список комментариев {{ $user->login }} (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Комментарии {{ $user->login }}</h1>

    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>
        <li class="breadcrumb-item active">Комментарии {{ $user->login }}</li>
    </ol>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">
                    <i class="fa fa-comment"></i>
                    <b><a href="/down/comment/{{ $data->relate_id }}/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})

                    <div class="float-right">
                        @if (isAdmin())
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ App\Models\Down::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>
                </div>

                <div>
                    {!! bbCode($data->text) !!}<br>

                    Написал: {!! profile($data->user) !!} <small>({{ dateFixed($data->created_at) }})</small><br>
                    @if (isAdmin())
                        <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                    @endif
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Комментарии не найдены!') !!}
    @endif
@stop
