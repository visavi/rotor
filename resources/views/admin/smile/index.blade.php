@extends('layout')

@section('title')
    Смайлы
@stop

@section('content')

    <h1>Смайлы</h1>

    @if ($smiles->isNotEmpty())
        <form action="/admin/smiles/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            @foreach($smiles as $smile)
            <img src="/uploads/smiles/{{ $smile->name }}" alt=""> — <b>{{ $smile->code }}</b><br>

            <input type="checkbox" name="del[]" value="{{ $smile->id }}"> <a href="/admin/smiles/edit/{{ $smile->id }}?page={{ $page['current'] }}">Редактировать</a><br>
            @endforeach

            <button class="btn btn-primary">Удалить выбранное</button>
        </form>

        {!! pagination($page) !!}

        Всего cмайлов: <b>{{ $page['total'] }}</b><br><br>

    @else
        {!! showError('Смайлы еще не загружены!') !!}
    @endif

    <i class="fa fa-upload"></i> <a href="/admin/smiles/create">Загрузить</a><br>

    <i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br>
@stop
