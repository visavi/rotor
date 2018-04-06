@extends('layout')

@section('title')
    {{ $find }} - Результаты поиска
@stop

@section('content')

    <h1>Результаты поиска</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blog">Блоги</a></li>
            <li class="breadcrumb-item"><a href="/blog/search">Поиск</a></li>
            <li class="breadcrumb-item active">Результаты поиска</li>
        </ol>
    </nav>

    <h3>Поиск запроса &quot;{{ $find }}&quot; в заголовке</h3>
    Найдено совпадений: <b>{{ $page->total }}</b><br><br>

    @foreach ($blogs as $data)

        <div class="b">
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/article/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
        </div>

        <div>
            Категория: <a href="/blog/{{ $data->category_id }}">{{ $data->name }}</a><br>
            Просмотров: {{ $data->visits }}<br>
            Автор: {!! profile($data->user) !!}  ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
