@extends('layout')

@section('title')
    Новости сайта (Стр. {{ $page['current']}})
@stop

@section('content')

    <h1>Новости сайта</h1>

    @if ($isModer)
        <div class="form"><a href="/admin/news">Управление новостями</a></div>
    @endif

    @if ($news->isNotEmpty())
        @foreach ($news as $data)
            <div class="b">
                <i class="fa fa-file-alt fa-lg text-muted"></i>
                <b><a href="/news/{{ $data->id }}">{{ $data->title }}</a></b><small> ({{ dateFixed($data->created_at) }})</small>
            </div>

            @if ($data->image)
                <div class="img">
                    <a href="/uploads/news/{{ $data->image }}">{!! resizeImage('uploads/news/', $data->image, ['size' => 75, 'alt' => $data->title]) !!}</a>
                </div>
            @endif

            <div>{!! bbCode($data->shortText()) !!}</div>
            <div>
                Добавлено: {!! profile($data->user) !!}<br>
                <a href="/news/{{ $data->id }}/comments">Комментарии</a> ({{ $data->comments }})
                <a href="/news/{{ $data->id }}/end">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Новостей еще нет!') !!}
    @endif

    <i class="fa fa-rss"></i> <a href="/news/rss">RSS подписка</a><br>
    <i class="fa fa-comment"></i> <a href="/news/allcomments">Комментарии</a><br>
@stop
