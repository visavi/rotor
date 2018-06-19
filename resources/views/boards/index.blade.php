@extends('layout')

@section('title')
    Объявления
@stop

@section('content')

    <h1>Объявления</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Объявления</li>
        </ol>
    </nav>

    @if ($items->isNotEmpty())

        @foreach ($items as $item)
            <div class="b"><b>{{ $item->title }}</b></div>
            <div class="message">{!! bbCode($item->text) !!}</div>
            <div>
                <span class="badge badge-pill badge-success">Цена: {{ $item->price }} ₽</span><br>
                Категория: <a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a><br>
                Автор: {!! $item->user->getProfile() !!}
            </div>
        @endforeach

        {!! pagination($page) !!}

    @else
        {!! showError('Объявлений еще нет!') !!}
    @endif

@stop
