@extends('layout')

@section('title')
    Последние комментарии
@stop

@section('content')

    <h1>Последние комментарии</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">Новости сайта</a></li>
            <li class="breadcrumb-item active">Последние комментарии</li>
        </ol>
    </nav>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="b">
                <i class="fa fa-comment"></i> <b><a href="/news/comment/{{ $data->relate_id }}/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
            </div>

            <div>
                {!! bbCode($data->text) !!}<br>
                Написал: {!! profile($data->user) !!} <small>({{ dateFixed($data->created_at) }})</small><br>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Комментарии не найдены!') !!}
    @endif
@stop
