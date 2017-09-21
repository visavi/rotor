@extends('layout')

@section('title')
    Новости сайта (Стр. {{ $page['current']}}) - @parent
@stop

@section('content')

    <h1>Новости сайта</h1>

    @if ($isModer)
        <div class="form"><a href="/admin/news">Управление новостями</a></div>
    @endif

    @if ($news->isNotEmpty())
        @foreach ($news as $data)
            <div class="b">
                {!! $data['closed'] == 0 ? '<i class="fa fa-plus-square-o"></i> ' : '<i class="fa fa-minus-square-o"></i>' !!}
                <b><a href="/news/{{ $data['id'] }}">{{ $data['title'] }}</a></b><small> ({{ dateFixed($data['created_at']) }})</small>
            </div>

            @if ($data['image'])
                <div class="img">
                    <a href="/uploads/news/{{ $data['image'] }}">{!! resizeImage('uploads/news/', $data['image'], 75, ['alt' => $data['title']]) !!}</a>
                </div>
            @endif

            @if (stristr($data['text'], '[cut]'))
                @php
                 $data['text'] = current(explode('[cut]', $data['text'])).' <a href="/news/'.$data['id'].'">Читать далее &raquo;</a>';
                @endphp
            @endif

            <div>{!! bbCode($data['text']) !!}</div>
            <div style="clear:both;">
                Добавлено: {!! profile($data->user) !!}<br>
                <a href="/news/{{ $data['id'] }}/comments">Комментарии</a> ({{ $data['comments'] }})
                <a href="/news/{{ $data['id'] }}/end">&raquo;</a>
            </div>
        @endforeach

        {{ pagination($page) }}
    @else
        {{ showError('Новостей еще нет!') }}
    @endif

    <i class="fa fa-rss"></i> <a href="/news/rss">RSS подписка</a><br>
    <i class="fa fa-comment"></i> <a href="/news/allcomments">Комментарии</a><br>
@stop
