@extends('layout')

@section('title')
    {{ $find }} - Результаты поиска
@stop

@section('content')

    <h1>Результаты поиска</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">Блоги</a></li>
            <li class="breadcrumb-item"><a href="/blogs/search">Поиск</a></li>
            <li class="breadcrumb-item active">Результаты поиска</li>
        </ol>
    </nav>

    <h3>Поиск запроса &quot;{{ $find }}&quot; в тексте</h3>
    Найдено совпадений: <b>{{ $page->total }}</b><br><br>

    @foreach ($blogs as $data)

        <div class="b">
            <i class="fa fa-pencil-alt"></i>
            <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
        </div>

        <?php
        if (utfStrlen($data->text) > 200):
            $data->text = strip_tags(bbCode($data->text), '<br>');
            $data->text = utfSubstr($data->text, 0, 200).'...';
            endif;
        ?>

        <div>
            {!! $data->text !!}<br>

            Категория: <a href="/blogs/{{ $data->category_id }}">{{ $data->name }}</a><br>
            Автор: {!! profile($data->user) !!}  ({{ dateFixed($data->created_at) }})
        </div>
    @endforeach

    {!! pagination($page) !!}
@stop
