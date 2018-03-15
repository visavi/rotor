@extends('layout')

@section('title')
    Загрузки - Новые комментарии (Стр. {{ $page['current'] }})
@stop

@section('content')

    <h1>Новые комментарии</h1>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="b">
                <i class="fa fa-comment"></i> <b><a href="/down/comment/{{ $data->relate_id }}/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
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
