@extends('layout')

@section('title')
    Блоги - Новые статьи (Стр. {{ $page->current }})
@stop

@section('content')

    <h1>Новые статьи</h1>

    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/article/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>

            <div>
                Категория: <a href="/blog/{{ $data->category_id }}">{{ $data->category->name }}</a><br>
                Просмотров: {{ $data->visits }}<br>
                Добавил: {!! profile($data->user) !!}  ({{  dateFixed($data->created_at) }})
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Опубликованных статей еще нет!') !!}
    @endif
@stop
