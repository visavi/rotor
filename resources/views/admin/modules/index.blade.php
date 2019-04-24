@extends('layout')

@section('title')
    Модули
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">Модули</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($moduleNames)
        @foreach ($moduleNames as $name => $moduleConfig)
            <i class="fas fa-plug"></i> <a class="font-weight-bold" href="/admin/modules/module?module={{ $name }}">{{ $moduleConfig['name'] }}</a> ({{ $name }})

            @if (isset($moduleInstall[$name]))
                <span class="badge badge-success">Установлен</span>

                @if ($moduleInstall[$name]->disabled)
                    <span class="badge badge-warning">Выключен</span>
                @endif

                @if (version_compare($moduleConfig['version'], $moduleInstall[$name]->version, '>'))
                    <span class="badge badge-info">Доступно обновление (v.{{ $moduleConfig['version'] }})</span>
                @endif
                <br>
            @else
                <span class="badge badge-danger">Не установлен</span><br>
            @endif
            {{ $moduleConfig['description'] }}<br>
            Версия: {{ $moduleConfig['version'] }}<br>
            Автор: {{ $moduleConfig['author'] }} <a href="{{ $moduleConfig['homepage'] }}">{{ $moduleConfig['homepage'] }}</a><hr>
        @endforeach
    @else
        {!! showError('Модули еще не загружены!') !!}
    @endif
@stop
