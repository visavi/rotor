@extends('layout')

@section('title')
    Список стикеров
@stop

@section('content')

    <h1>Список стикеров</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Список стикеров</li>
        </ol>
    </nav>

    <h1>Стикеры</h1>

    <i class="far fa-smile"></i>  <b><a href="/stickers/0">Общие</a></b><br>
    @if ($categories->isNotEmpty())
        @foreach($categories as $category)
            <i class="far fa-smile"></i>  <b><a href="/stickers/{{ $category->id }}">{{ $category->name }}</a></b><br>
        @endforeach
    @else
        {!! showError('Категории еще не созданы!') !!}
    @endif
@stop
