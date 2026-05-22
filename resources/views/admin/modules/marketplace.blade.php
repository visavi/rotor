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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted">{{ __('admin.modules.marketplace_hint') }}</span>
        <a href="{{ route('admin.modules.marketplace', ['refresh' => 1]) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-sync-alt"></i> {{ __('main.refresh') }}
        </a>
    </div>

    @if ($available)
        @foreach ($available as $name => $info)
            @php
                $installed   = $modules->has($name);
                $isActive    = $installed && $modules[$name]->active;
                $localExists = in_array($name, $moduleNames, true);
                $hasUpdate   = $installed && isset($info['version']) && version_compare($info['version'], $modules[$name]->version, '>');
                $requires    = $info['requires'] ?? null;
                $compatible  = ! $requires || version_compare(ROTOR_VERSION, $requires, '>=');
            @endphp
            <div class="section mb-3 shadow">
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
                    <div class="d-flex gap-2 align-items-center">
                        @if (! $compatible)
                            <span class="badge bg-danger" title="{{ __('admin.modules.requires') }}: {{ $requires }}">
                                {{ __('admin.modules.incompatible') }}
                            </span>
                        @else
                            @if ($installed && ! $isActive)
                                <span class="badge bg-warning text-dark">{{ __('main.disabled') }}</span>
                            @endif
                            @if ($hasUpdate)
                                <span class="badge bg-info">{{ __('main.update_available') }}</span>
                            @endif
                        @endif

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
                        @elseif ($installed)
                            <span class="badge bg-success">{{ __('main.installed') }}</span>
                        @else
                            <a href="/admin/modules/module?module={{ $name }}" class="btn btn-sm btn-outline-success">
                                {{ __('main.install') }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="section-content">
                    {{ $info['description'] ?? '' }}<br>
                    {{ __('main.version') }}: {{ $info['version'] ?? '—' }}<br>
                    {{ __('main.author') }}: {{ $info['author'] ?? '—' }}
                    @if (! empty($info['homepage']))
                        <a href="{{ $info['homepage'] }}">{{ $info['homepage'] }}</a>
                    @endif
                    <br>
                    <small class="text-muted">{{ __('admin.registries.source') }}: {{ $info['registry'] }}</small>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('admin.registries.no_modules')) }}

        <div class="text-center mt-3">
            <a href="{{ route('admin.registries.index') }}" class="btn btn-primary">
                {{ __('admin.registries.add_registry') }}
            </a>
        </div>
    @endif
@stop
