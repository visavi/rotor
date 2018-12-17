@extends('layout')

@section('title')
    {{ $category->name ?? 'Общие стикеры' }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            @if ($category)
                <li class="breadcrumb-item"><a href="/stickers">Стикеры</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $category->name ?? 'Общие стикеры' }}</li>
        </ol>
    </nav>

    <h1>{{ $category->name ?? 'Общие стикеры' }}</h1>

    @if ($stickers)
        @foreach ($stickers as $sticker)
            <div class="bg-light p-2 mb-1 border">
                <img src="{{ $sticker['name'] }}" alt=""><br>
                <b>{{ $sticker['code'] }}</b>
            </div>
        @endforeach

        {!! pagination($page) !!}

        Всего стикеров: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Стикеры не найдены!') !!}
    @endif
@stop
