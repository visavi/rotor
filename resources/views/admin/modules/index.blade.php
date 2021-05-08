@extends('layout')

@section('title', __('index.modules'))

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
            <div class="section mb-3 shadow">
                <div class="section-title">

                    <i class="fas fa-plug"></i> <a class="fw-bold" href="/admin/modules/module?module={{ $name }}">{{ $moduleConfig['name'] }}</a> ({{ $name }})
                </div>

                <div class="section-content">
                    @if (isset($moduleInstall[$name]))
                        <span class="badge bg-success">{{ __('main.installed') }}</span>

                        @if ($moduleInstall[$name]->disabled)
                            <span class="badge bg-warning">{{ __('main.disabled') }}</span>
                        @endif

                        @if (version_compare($moduleConfig['version'], $moduleInstall[$name]->version, '>'))
                            <span class="badge bg-info">{{ __('main.update_available') }} (v.{{ $moduleConfig['version'] }})</span>
                        @endif
                        <br>
                    @else
                        <span class="badge bg-danger">{{ __('main.not_installed') }}</span><br>
                    @endif
                    {{ $moduleConfig['description'] }}<br>
                    {{ __('main.version') }}: {{ $moduleConfig['version'] }}<br>
                    {{ __('main.author') }}: {{ $moduleConfig['author'] }} <a href="{{ $moduleConfig['homepage'] }}">{{ $moduleConfig['homepage'] }}</a>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('admin.modules.empty_modules')) }}
    @endif
@stop
