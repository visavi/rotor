@extends('layout')

@section('title', __('index.modules'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.modules') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('admin/modules/_tabs')

    @if (! empty($failedModules))
        <div class="alert alert-danger">
            <i class="fas fa-triangle-exclamation"></i> {{ __('admin.modules.load_failed') }}:
            <strong>{{ implode(', ', array_keys($failedModules)) }}</strong>.
            {{ __('admin.modules.load_failed_hint') }}
        </div>
    @endif

    @if ($moduleNames)
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-sm btn-primary active" data-module-filter data-filter="all" data-class-active="btn-primary" data-class-idle="btn-outline-primary">{{ __('main.all') }} <span class="badge bg-white text-primary">{{ $counts['all'] }}</span></button>
            <button class="btn btn-sm btn-outline-success" data-module-filter data-filter="installed" data-class-active="btn-success" data-class-idle="btn-outline-success">{{ __('main.installed') }} <span class="badge bg-success">{{ $counts['installed'] }}</span></button>
            <button class="btn btn-sm btn-outline-secondary" data-module-filter data-filter="disabled" data-class-active="btn-secondary" data-class-idle="btn-outline-secondary">{{ __('main.disabled') }} <span class="badge bg-warning text-dark">{{ $counts['disabled'] }}</span></button>
            <button class="btn btn-sm btn-outline-danger" data-module-filter data-filter="not-installed" data-class-active="btn-danger" data-class-idle="btn-outline-danger">{{ __('main.not_installed') }} <span class="badge bg-danger">{{ $counts['not-installed'] }}</span></button>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="{{ __('main.search') }}" autocomplete="off" data-module-search>
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-sm" data-module-sort>
                    <option value="name">{{ __('main.sort') }}: {{ __('main.title') }}</option>
                    <option value="status">{{ __('main.sort') }}: {{ __('main.status') }}</option>
                    <option value="version">{{ __('main.sort') }}: {{ __('main.version') }}</option>
                </select>
            </div>
        </div>

        <div data-module-list>
            @foreach ($moduleNames as $name => $moduleConfig)
                @php
                    $isInstalled   = isset($moduleInstall[$name]);
                    $isActive      = $isInstalled && $moduleInstall[$name]->active;
                    $activeStatus  = $isActive ? 'installed' : 'disabled';
                    $status        = $isInstalled ? $activeStatus : 'not-installed';
                    $registryVer   = $registryModules[$name]['version'] ?? null;
                    $hasRegistryUpdate = $isInstalled && $registryVer && version_compare($registryVer, $moduleInstall[$name]->version, '>');
                    $sortVersion   = $moduleInstall[$name]->version ?? ($moduleConfig['version'] ?? '0');
                    $sortStatus    = $status === 'installed' ? 0 : ($status === 'disabled' ? 1 : 2);
                    $searchText    = mb_strtolower(trim(($moduleConfig['name'] ?? $name) . ' ' . $name . ' ' . ($moduleConfig['description'] ?? '') . ' ' . ($moduleConfig['author'] ?? '')));
                @endphp
                <div class="section mb-3 shadow" data-module-card
                     data-status="{{ $status }}"
                     data-search="{{ $searchText }}"
                     data-name="{{ mb_strtolower($moduleConfig['name'] ?? $name) }}"
                     data-version="{{ $sortVersion }}"
                     data-sort-status="{{ $sortStatus }}">
                    <div class="section-title">
                        <i class="fas fa-plug {{ $isActive ? 'text-success' : 'text-muted' }}"></i> <a class="fw-bold" href="/admin/modules/module?module={{ $name }}">{{ $moduleConfig['name'] ?? $name }}</a> ({{ $name }})
                    </div>

                    <div class="section-content">
                        @if ($isInstalled)
                            <span class="badge bg-success">{{ __('main.installed') }}</span>

                            @if (! $isActive)
                                <span class="badge bg-warning text-dark">{{ __('main.disabled') }}</span>
                            @endif

                            @if (isset($failedModules[$name]))
                                <span class="badge bg-danger" title="{{ $failedModules[$name] }}">{{ __('admin.modules.load_error') }}</span>
                            @endif

                            @if (version_compare($moduleConfig['version'], $moduleInstall[$name]->version, '>'))
                                <span class="badge bg-info">{{ __('main.update_available') }} (v.{{ $moduleConfig['version'] }})</span>
                            @elseif ($hasRegistryUpdate)
                                <span class="badge bg-info">{{ __('main.update_available') }} (v.{{ $registryVer }})</span>
                            @endif
                            <br>
                        @else
                            <span class="badge bg-danger">{{ __('main.not_installed') }}</span><br>
                        @endif
                        {{ $moduleConfig['description'] ?? '' }}<br>
                        {{ __('main.version') }}: {{ $moduleInstall[$name]->version ?? ($moduleConfig['version'] ?? '') }}<br>
                        {{ __('main.author') }}: {{ $moduleConfig['author'] ?? '' }} <a href="{{ $moduleConfig['homepage'] ?? '' }}">{{ $moduleConfig['homepage'] ?? '' }}</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-none" data-module-empty>
            {{ showError(__('main.nothing_found')) }}
        </div>
    @else
        {{ showError(__('admin.modules.empty_modules')) }}
    @endif

    @include('admin/modules/_filter')
@stop
