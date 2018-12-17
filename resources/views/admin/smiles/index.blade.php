@extends('layout')

@section('title')
    Смайлы
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Смайлы</li>
        </ol>
    </nav>

    <div class="float-right">
        <a class="btn btn-success" href="/admin/smiles/create">Загрузить</a>
    </div><br>

    <h1>Смайлы</h1>

    <i class="far fa-smile"></i>  <b><a href="/admin/smiles/0">Общие</a></b><br>
    @if ($categories->isNotEmpty())
        @foreach($categories as $category)
            <i class="far fa-smile"></i>  <b><a href="/admin/smiles/{{ $category->id }}">{{ $category->name }}</a></b><br>
        @endforeach
    @else
        {!! showError('Категории еще не созданы!') !!}
    @endif
@stop
