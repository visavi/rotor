@extends('layout')

@section('title')
    Загрузки - Новые файлы (Стр. {{ $page->current }})
@stop

@section('content')
    <h1>Новые файлы</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/load">Загрузки</a></li>
            <li class="breadcrumb-item active">Новые файлы</li>
        </ol>
    </nav>

    @if ($downs->isNotEmpty())
        @foreach ($downs as $down)
            <?php $rating = $down->rated ? round($down->rating / $down->rated, 1) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/down/{{ $down->id }}">{{ $down->title }}</a></b> ({{ $rating }})
            </div>
            <div>
                Категория: <a href="/load/{{ $down->category->id }}">{{ $down->category->name }}</a><br>
                Скачиваний: {{ $down->loads }}<br>
                Автор: {!! profile($down->user) !!} ({{ dateFixed($down->created_at) }})
            </div>

        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Опубликованных файлов еще нет!') !!}
    @endif
@stop
