@extends('layout')

@section('title')
    Последние комментарии - @parent
@stop

@section('content')

    <h1>Последние комментарии</h1>

    @if ($comments->isNotEmpty())
        @foreach ($comments as $data)
            <div class="b">
                <i class="fa fa-comment"></i> <b><a href="/news/{{ $data['relate_id'] }}/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({{ $data['comments'] }})
            </div>

            <div>
                {!! App::bbCode($data['text']) !!}<br>
                Написал: {!! profile($data['user']) !!} <small>({{ date_fixed($data['created_at']) }})</small><br>

                @if (is_admin())
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                @endif
            </div>
        @endforeach

        {{ App::pagination($page) }}
    @else
        {{ App::showError('Комментарии не найдены!') }}
    @endif
    <i class="fa fa-arrow-circle-up"></i> <a href="/news">К новостям</a><br>
@stop
