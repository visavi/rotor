@extends('layout')

@section('title')
    Редактирование категории {{ $category->name }}
@stop

@section('content')

    <h1>Редактирование категории {{ $category->name }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers">Стикеры</a></li>
            <li class="breadcrumb-item"><a href="/admin/stickers/{{ $category->id }}">{{ $category->name }}</a></li>
            <li class="breadcrumb-item active">Редактирование категории</li>
        </ol>
    </nav>

    <div class="form mb-3">
        <form action="/admin/stickers/edit/{{ $category->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('name') }}">
                <label for="name">Название:</label>
                <input class="form-control" name="name" id="name" maxlength="50" value="{{ getInput('name', $category->name) }}" required>
                {!! textError('name') !!}
            </div>
            <button class="btn btn-primary">Изменить</button>
        </form>
    </div>
@stop
