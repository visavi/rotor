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

    @if ($moduleNames)
        @foreach ($moduleNames as $name => $module)
            <i class="fas fa-plug"></i> <a class="font-weight-bold" href="/admin/modules/module?module={{ $name }}">{{ $module['name'] }}</a> ({{ $name }})
            @if (isset($moduleActive[$name]))
                <span class="badge badge-success">Включен</span><br>

                @if (version_compare($module['version'], $moduleActive[$name], '>'))
                    <span class="badge badge-warning">Доступно обновление (v.{{ $module['version'] }})</span><br>
                @endif
            @else
                <span class="badge badge-danger">Выключен</span><br>
            @endif
            {{ $module['description'] }}<br>
            Версия: {{ $module['version'] }}<br>
            Автор: {{ $module['author'] }} <a href="{{ $module['homepage'] }}">{{ $module['homepage'] }}</a><hr>
        @endforeach
    @else
        {!! showError('Модули еще не загружены!') !!}
    @endif
@stop
