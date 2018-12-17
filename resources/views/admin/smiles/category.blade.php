@extends('layout')

@section('title')
    {{ $category->name ?? 'Общие смайлы' }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            @if ($category)
                <li class="breadcrumb-item"><a href="/admin/smiles">Смайлы</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $category->name ?? 'Общие смайлы' }}</li>
        </ol>
    </nav>

    <div class="float-right">
        <a class="btn btn-success" href="/admin/smiles/create?cid={{ $category->id ?? 0 }}">Загрузить</a>
    </div><br>

    <h1>{{ $category->name ?? 'Общие смайлы' }}</h1>

    @if ($smiles->isNotEmpty())
        @foreach($smiles as $smile)
            <div class="bg-light p-2 mb-1 border">
                <div class="float-right">
                    <a href="/admin/smiles/edit/{{ $smile->id }}?page={{ $page->current }}" data-toggle="tooltip" title="Редактировать"><i class="fa fa-pencil-alt"></i></a>

                    <a href="/admin/smiles/delete/{{ $smile->id }}?page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}" data-toggle="tooltip" title="Удалить" onclick="return confirm('Вы уверены что хотите удалить данный смайл')"><i class="fa fa-times"></i></a>
                </div>

                <img src="{{ $smile->name }}" alt=""><br>
                <b>{{ $smile->code }}</b>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего смайлов: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Смайлы еще не загружены!') !!}
    @endif
@stop
