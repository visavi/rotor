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
        <b>Список миграций</b><br>
        @foreach ($migrations as $migration)
            <i class="fas fa-database"></i> {{ $migration }}<br>
        @endforeach
    @endif

    @if ($module['symlinks'])
        <b>Список симлинков</b><br>
        @foreach ($module['symlinks'] as $key => $symlink)
            <i class="fas fa-images"></i> {{ $key }} -> {{ $symlink }}<br>
        @endforeach
    @endif

    <br>
    <a class="btn btn-primary" href="/admin/modules/install?module={{ $moduleName }}">Установить модуль</a>
@stop
