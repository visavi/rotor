@extends('layout')

@section('title')
    {{ __('index.modules') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.modules') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($moduleNames)
        @foreach ($moduleNames as $name => $moduleConfig)
            <i class="fas fa-plug"></i> <a class="font-weight-bold" href="/admin/modules/module?module={{ $name }}">{{ $moduleConfig['name'] }}</a> ({{ $name }})

            @if (isset($moduleInstall[$name]))
                <span class="badge badge-success">{{ __('main.installed') }}</span>

                @if ($moduleInstall[$name]->disabled)
                    <span class="badge badge-warning">{{ __('main.disabled') }}</span>
                @endif

                @if (version_compare($moduleConfig['version'], $moduleInstall[$name]->version, '>'))
                    <span class="badge badge-info">{{ __('main.update_available') }} (v.{{ $moduleConfig['version'] }})</span>
                @endif
                <br>
            @else
                <span class="badge badge-danger">{{ __('main.not_installed') }}</span><br>
            @endif
            {{ $moduleConfig['description'] }}<br>
            {{ __('main.version') }}: {{ $moduleConfig['version'] }}<br>
            {{ __('main.author') }}: {{ $moduleConfig['author'] }} <a href="{{ $moduleConfig['homepage'] }}">{{ $moduleConfig['homepage'] }}</a><hr>
        @endforeach
    @else
        {!! showError(__('admin.modules.empty_modules')) !!}
    @endif
@stop
