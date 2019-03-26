@extends('layout')

@section('title')
    Список всех комментариев (Стр. {{ $page->current}})
@stop

@section('header')
    <h1>Список всех комментариев</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/photos">Галерея</a></li>
            <li class="breadcrumb-item active">Список всех комментариев</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">
                    <i class="fa fa-comment"></i> <b><a href="/photos/comment/{{ $data->relate_id }}/{{ $data->id }}">{{ $data->title }}</a></b>

                    @if (isAdmin())
                        <a href="#" class="float-right" onclick="return deleteComment(this)" data-rid="{{ $data->relate_id }}" data-id="{{ $data->id }}" data-type="{{ App\Models\Photo::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-times"></i></a>
                    @endif
                </div>

                <div>
                    {!! bbCode($data->text) !!}<br>
                    Написал: <b>{!! $data->user->getProfile() !!}</b> <small>({{ dateFixed($data->created_at) }})</small><br>

                    @if (isAdmin())
                        <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                    @endif
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}

    @else
        {!! showError(trans('main.empty_comments')') !!}
    @endif
@stop
