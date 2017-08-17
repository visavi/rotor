@extends('layout')

@section('title')
    Список всех комментариев (Стр. {{ $page['current']}}) - @parent
@stop

@section('content')

    <h1>Список всех комментариев</h1>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="post">
                <div class="b">
                    <i class="fa fa-comment"></i> <b><a href="/gallery/{{ $data['relate_id']}}/{{ $data['id'] }}/comment">{{ $data['title'] }}</a></b>

                    @if (is_admin())
                        <a href="#" class="pull-right" onclick="return deleteComment(this)" data-rid="{{ $data['relate_id'] }}" data-id="{{ $data['id'] }}" data-type="{{ Photo::class }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить"><i class="fa fa-remove"></i></a>
                    @endif
                </div>

                <div>
                    {!! App::bbCode($data['text']) !!}<br>
                    Написал: <b>{!! profile($data['user']) !!}</b> <small>({{ date_fixed($data['created_at']) }})</small><br>

                    @if (is_admin())
                        <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                    @endif
                </div>
            </div>
        @endforeach

        {{ App::pagination($page) }}

    @else
        {{ show_error('Комментариев еще нет!') }}
    @endif

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br>
@stop
