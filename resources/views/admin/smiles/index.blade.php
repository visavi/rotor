@extends('layout')

@section('title')
    Смайлы
@stop

@section('content')

    <div class="float-right">
        <a class="btn btn-success" href="/admin/smiles/create">Загрузить</a>
    </div><br>

    <h1>Смайлы</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Смайлы</li>
        </ol>
    </nav>

    @if ($smiles->isNotEmpty())
        <form action="/admin/smiles/delete?page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach($smiles as $smile)
                <img src="{{ $smile->name }}" alt=""> — <b>{{ $smile->code }}</b><br>

                <input type="checkbox" name="del[]" value="{{ $smile->id }}"> <a href="/admin/smiles/edit/{{ $smile->id }}?page={{ $page->current }}">Редактировать</a><br>
            @endforeach

            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </form>

        {!! pagination($page) !!}

        Всего cмайлов: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Смайлы еще не загружены!') !!}
    @endif
@stop
