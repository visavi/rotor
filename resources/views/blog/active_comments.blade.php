@extends('layout')

@section('title')
    Список всех комментариев {{ $user->login }} (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Список всех комментариев {{ $user->login }}</h1>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">

                    <i class="fa fa-comment"></i>
                    <b><a href="/article/{{ $data->relate_id}}/{{ $data->id}}">{{ $data->title }}</a></b> ({{ $data->comments }})

                    <div class="float-right">
                        @if (isAdmin())
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ Blog::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>

                </div>
                <div>
                    {!! bbCode($data->text) !!}
                    <br>

                    Написал: {!! profile($data->user) !!} <small>({{ dateFixed($data->time) }})</small><br>
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
