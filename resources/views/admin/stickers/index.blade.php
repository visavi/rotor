@extends('layout')

@section('title')
    Стикеры
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Стикеры</li>
        </ol>
    </nav>

    <div class="float-right">
        <a class="btn btn-success" href="/admin/stickers/create">Загрузить</a>
    </div><br>

    <h1>Стикеры</h1>

    <i class="far fa-smile"></i>  <b><a href="/admin/stickers/0">Общие</a></b><br>
    @if ($categories->isNotEmpty())
        @foreach($categories as $category)
            <i class="far fa-smile"></i>  <b><a href="/admin/stickers/{{ $category->id }}">{{ $category->name }}</a></b><br>
        @endforeach
    @else
        {!! showError('Категории еще не созданы!') !!}
    @endif
@stop
