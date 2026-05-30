@extends('layout')

@section('title', __('admin.modules.marketplace'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.modules.index') }}">{{ __('index.modules') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.modules.marketplace') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('admin/modules/_tabs')

    @if ($available)
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <button class="btn btn-sm btn-primary active" data-module-filter data-filter="all" data-class-active="btn-primary" data-class-idle="btn-outline-primary">{{ __('main.all') }} <span class="badge bg-white text-primary">{{ $counts['all'] }}</span></button>
            <button class="btn btn-sm btn-outline-success" data-module-filter data-filter="installed" data-class-active="btn-success" data-class-idle="btn-outline-success">{{ __('main.installed') }} <span class="badge bg-success">{{ $counts['installed'] }}</span></button>
            <button class="btn btn-sm btn-outline-secondary" data-module-filter data-filter="disabled" data-class-active="btn-secondary" data-class-idle="btn-outline-secondary">{{ __('main.disabled') }} <span class="badge bg-warning text-dark">{{ $counts['disabled'] }}</span></button>
            <button class="btn btn-sm btn-outline-danger" data-module-filter data-filter="not-installed" data-class-active="btn-danger" data-class-idle="btn-outline-danger">{{ __('main.not_installed') }} <span class="badge bg-danger">{{ $counts['not-installed'] }}</span></button>

            <a href="{{ route('admin.modules.marketplace', ['refresh' => 1]) }}" class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="fas fa-sync-alt"></i> {{ __('main.refresh') }}
            </a>
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
        @foreach ($available as $name => $info)
            @php
                $installed   = $modules->has($name);
                $isActive    = $installed && $modules[$name]->active;
                $localExists = in_array($name, $moduleNames, true);
                $hasUpdate   = $installed && isset($info['version']) && version_compare($info['version'], $modules[$name]->version, '>');
                $requires    = $info['requires'] ?? null;
                $compatible  = ! $requires || version_compare(ROTOR_VERSION, $requires, '>=');
                $searchText  = mb_strtolower(trim(($info['name'] ?? $name) . ' ' . $name . ' ' . ($info['description'] ?? '') . ' ' . ($info['author'] ?? '')));
                $status      = $installed && $localExists ? ($isActive ? 'installed' : 'disabled') : 'not-installed';
                $sortStatus  = $status === 'installed' ? 0 : ($status === 'disabled' ? 1 : 2);
            @endphp
            <div class="section mb-3 shadow" data-module-card
                 data-status="{{ $status }}"
                 data-search="{{ $searchText }}"
                 data-name="{{ mb_strtolower($info['name'] ?? $name) }}"
                 data-version="{{ $info['version'] ?? '0' }}"
                 data-sort-status="{{ $sortStatus }}">
                <div class="section-title d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-plug"></i>
                        @if ($localExists)
                            <a class="fw-bold" href="/admin/modules/module?module={{ $name }}">{{ $info['name'] ?? $name }}</a>
                        @else
                            <span class="fw-bold">{{ $info['name'] ?? $name }}</span>
                        @endif
                        ({{ $name }})
                    </div>
                    <div>
                        @if (! $compatible)
                            {{-- incompatible: no action buttons --}}
                        @elseif (! $localExists)
                            <form action="{{ route('admin.modules.download') }}" method="post">
                                @csrf
                                <input type="hidden" name="url" value="{{ $info['download_url'] ?? '' }}">
                                <button class="btn btn-sm btn-success" {{ empty($info['download_url']) ? 'disabled' : '' }}>
                                    <i class="fas fa-download"></i> {{ __('main.install') }}
                                </button>
                            </form>
                        @elseif ($hasUpdate)
                            <form action="{{ route('admin.modules.download') }}" method="post">
                                @csrf
                                <input type="hidden" name="url" value="{{ $info['download_url'] ?? '' }}">
                                <button class="btn btn-sm btn-info" {{ empty($info['download_url']) ? 'disabled' : '' }}>
                                    <i class="fas fa-arrow-up"></i> {{ __('main.update') }}
                                </button>
                            </form>
                        @elseif (! $installed)
                            <a href="/admin/modules/module?module={{ $name }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plug"></i> {{ __('main.install') }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    @if (! $compatible)
                        <span class="badge bg-danger">{{ __('admin.modules.incompatible') }}</span>
                    @else
                        @if ($installed && ! $localExists)
                            <span class="badge bg-danger">{{ __('admin.modules.files_missing') }}</span>
                        @elseif ($installed)
                            <span class="badge bg-success">{{ __('main.installed') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('main.not_installed') }}</span>
                        @endif
                        @if ($installed && $localExists && ! $isActive)
                            <span class="badge bg-warning text-dark">{{ __('main.disabled') }}</span>
                        @endif
                        @if ($hasUpdate)
                            <span class="badge bg-info">{{ __('main.update_available') }}</span>
                        @endif
                        @if (! empty($info['conflict']))
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-exclamation-triangle"></i> {{ __('admin.modules.conflict') }}: {{ implode(', ', $info['conflict']) }}
                            </span>
                        @endif
                    @endif
                    <br>
                    {{ $info['description'] ?? '' }}<br>
                    {{ __('main.version') }}: {{ $info['version'] ?? '—' }}<br>
                    {{ __('main.author') }}: {{ $info['author'] ?? '—' }}
                    @if (! empty($info['homepage']))
                        <a href="{{ $info['homepage'] }}">{{ $info['homepage'] }}</a>
                    @endif
                    <br>
                    @if ($requires)
                        <small class="{{ $compatible ? 'text-muted' : 'text-danger' }}">
                            {{ __('admin.modules.requires') }}: Rotor >= {{ $requires }}
                        </small><br>
                    @endif
                    @if (! empty($info['latest_version']))
                        <small class="text-warning">
                            {{ __('admin.modules.latest_version') }}: {{ $info['latest_version'] }}
                            @if (! empty($info['latest_requires']))
                                ({{ __('admin.modules.requires') }}: Rotor >= {{ $info['latest_requires'] }})
                            @endif
                        </small><br>
                    @endif
                    <small class="text-muted">{{ __('admin.registries.source') }}: {{ $info['registry'] }}</small>
                </div>
            </div>
        @endforeach
        </div>

        <div class="d-none" data-module-empty>
            {{ showError(__('main.nothing_found')) }}
        </div>

        @include('admin/modules/_filter')
    @else
        {{ showError(__('admin.registries.no_modules')) }}

        <div class="text-center mt-3">
            <a href="{{ route('admin.registries.index') }}" class="btn btn-primary">
                {{ __('admin.registries.add_registry') }}
            </a>
            <a href="{{ route('admin.modules.marketplace', ['refresh' => 1]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt"></i> {{ __('main.refresh') }}
            </a>
        </div>
    @endif
@stop
