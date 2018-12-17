@extends('layout')

@section('title')
    {{ $category->name ?? 'Общие смайлы' }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            @if ($category)
                <li class="breadcrumb-item"><a href="/smiles">Смайлы</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $category->name ?? 'Общие смайлы' }}</li>
        </ol>
    </nav>

    <h1>{{ $category->name ?? 'Общие смайлы' }}</h1>

    @if ($smiles)
        @foreach ($smiles as $smile)
            <div class="bg-light p-2 mb-1 border">
                <img src="{{ $smile['name'] }}" alt=""><br>
                <b>{{ $smile['code'] }}</b>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего смайлов: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Смайлы не найдены!') !!}
    @endif
@stop
