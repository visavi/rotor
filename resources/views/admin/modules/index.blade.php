@extends('layout')

@section('title')
    Модули
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Модули</li>
        </ol>
    </nav>

    <h1>Модули</h1>

    @if ($modules)
        @foreach ($modules as $module)
            <i class="fas fa-plug"></i> <a href="/admin/modules/module?module={{ $module }}">{{ $module }}</a>
        @endforeach
    @else
        {!! showError('Модули еще не загружены!') !!}
    @endif
@stop
