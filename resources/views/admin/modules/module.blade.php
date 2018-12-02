@extends('layout')

@section('title')
    Модуль {{ $module['name'] }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/modules">Модули</a></li>
            <li class="breadcrumb-item active">Модуль {{ $module['name'] }}</li>
        </ol>
    </nav>

    <h1>Модуль {{ $module['name'] }}</h1>

    {{ $module['name'] }}<br>
    {{ $module['description'] }}<br>
    Автор: {{ $module['author'] }} <a href="{{ $module['homepage'] }}">{{ $module['homepage'] }}</a><br><br>

    @if ($migrations)
        <i class="fas fa-database"></i> <a href="">Выполнить миграции</a><br>
    @endif

    @if ($images)
        <i class="fas fa-images"></i> <a href="">Создать симлинк для изображений</a><br>
    @endif
@stop
