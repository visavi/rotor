@extends('layout')

@section('title')
    Модуль {{ $module }}
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item"><a href="/admin/modules">Модули</a></li>
            <li class="breadcrumb-item active">Модуль {{ $module }}</li>
        </ol>
    </nav>

    <h1>Модуль {{ $module }}</h1>

    @if ($migrations)
        <i class="fas fa-database"></i> <a href="">Выполнить миграции</a><br>
    @endif

    @if ($images)
        <i class="fas fa-images"></i> <a href="">Создать симлинк для изображений</a><br>
    @endif
@stop
