@extends('layout')

@section('title')
    Список смайлов
@stop

@section('content')

    <h1>Список смайлов</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Список смайлов</li>
        </ol>
    </nav>

    <h1>Смайлы</h1>

    <i class="far fa-smile"></i>  <b><a href="/smiles/0">Общие</a></b><br>
    @if ($categories->isNotEmpty())
        @foreach($categories as $category)
            <i class="far fa-smile"></i>  <b><a href="/smiles/{{ $category->id }}">{{ $category->name }}</a></b><br>
        @endforeach
    @else
        {!! showError('Категории еще не созданы!') !!}
    @endif
@stop
