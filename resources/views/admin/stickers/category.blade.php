@extends('layout')

@section('title')
    {{ $category->name ?? 'Общие стикеры' }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            @if ($category)
                <li class="breadcrumb-item"><a href="/admin/stickers">Стикеры</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $category->name ?? 'Общие стикеры' }}</li>
        </ol>
    </nav>

    <div class="float-right">
        <a class="btn btn-success" href="/admin/stickers/create?cid={{ $category->id ?? 0 }}">Загрузить</a>
    </div><br>

    <h1>{{ $category->name ?? 'Общие стикеры' }}</h1>

    @if ($stickers->isNotEmpty())
        @foreach($stickers as $sticker)
            <div class="bg-light p-2 mb-1 border">
                <div class="float-right">
                    <a href="/admin/stickers/edit/{{ $sticker->id }}?page={{ $page->current }}" data-toggle="tooltip" title="Редактировать"><i class="fa fa-pencil-alt"></i></a>

                    <a href="/admin/stickers/delete/{{ $sticker->id }}?page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить" onclick="return confirm('Вы уверены что хотите удалить данный стикер')"><i class="fa fa-times"></i></a>
                </div>

                <img src="{{ $sticker->name }}" alt=""><br>
                <b>{{ $sticker->code }}</b>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего стикеров: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Стикеры еще не загружены!') !!}
    @endif
@stop
