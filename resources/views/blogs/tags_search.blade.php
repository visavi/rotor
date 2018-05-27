@extends('layout')

@section('title')
    Поиск по тегам
@stop

@section('content')
    <h1>Поиск по тегам</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">Блоги</a></li>
            <li class="breadcrumb-item"><a href="/blogs/tags">Облако тегов</a></li>
            <li class="breadcrumb-item active">Поиск по тегам</li>
        </ol>
    </nav>

    <h3>Поиск запроса &quot;{{ $tag }}&quot; в метках</h3>
    Найдено совпадений: <b>{{ $page->total }}</b><br>

    @foreach($blogs as $data)

        <div class="b">
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> (<?=formatNum($data->rating)?>)
        </div>

        <div>
            Категория: <a href="/blogs/{{ $data->category_id }}">{{ $data->name }}</a><br>
            Просмотров: {{ $data->visits }}<br>
            Метки: {{ $data->tags }}<br>
            Автор: {!! profile($data->user) !!}  ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
