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

    @if ($moduleNames)
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="btn btn-sm btn-primary js-module-filter active" data-filter="all">{{ __('main.all') }} <span class="badge bg-white text-primary">{{ $counts['all'] }}</span></button>
            <button class="btn btn-sm btn-outline-success js-module-filter" data-filter="installed">{{ __('main.installed') }} <span class="badge bg-success">{{ $counts['installed'] }}</span></button>
            <button class="btn btn-sm btn-outline-secondary js-module-filter" data-filter="disabled">{{ __('main.disabled') }} <span class="badge bg-warning text-dark">{{ $counts['disabled'] }}</span></button>
            <button class="btn btn-sm btn-outline-danger js-module-filter" data-filter="not-installed">{{ __('main.not_installed') }} <span class="badge bg-danger">{{ $counts['not-installed'] }}</span></button>
        </div>

        <div id="modules-list">
            @foreach ($moduleNames as $name => $moduleConfig)
                @php
                    $isInstalled   = isset($moduleInstall[$name]);
                    $isActive      = $isInstalled && $moduleInstall[$name]->active;
                    $activeStatus  = $isActive ? 'installed' : 'disabled';
                    $status        = $isInstalled ? $activeStatus : 'not-installed';
                    $registryVer   = $registryModules[$name]['version'] ?? null;
                    $hasRegistryUpdate = $isInstalled && $registryVer && version_compare($registryVer, $moduleInstall[$name]->version, '>');
                @endphp
                <div class="section mb-3 shadow js-module-card" data-status="{{ $status }}">
                    <div class="section-title">
                        <i class="fas fa-plug {{ $isActive ? 'text-success' : 'text-muted' }}"></i> <a class="fw-bold" href="/admin/modules/module?module={{ $name }}">{{ $moduleConfig['name'] ?? $name }}</a> ({{ $name }})
                    </div>

                    <div class="section-content">
                        @if ($isInstalled)
                            <span class="badge bg-success">{{ __('main.installed') }}</span>

                            @if (! $isActive)
                                <span class="badge bg-warning text-dark">{{ __('main.disabled') }}</span>
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

        <div id="modules-empty" class="d-none">
            {{ showError(__('admin.modules.empty_modules')) }}
        </div>
    @else
        {{ showError(__('admin.modules.empty_modules')) }}
    @endif

    @push('scripts')
        <script>
            document.querySelectorAll('.js-module-filter').forEach(btn => {
                btn.addEventListener('click', function () {
                    const filter = this.dataset.filter;

                    document.querySelectorAll('.js-module-filter').forEach(b => {
                        b.classList.remove('active', 'btn-primary', 'btn-success', 'btn-secondary', 'btn-danger');
                        b.classList.add(
                            b.dataset.filter === 'all'           ? 'btn-outline-primary'    :
                            b.dataset.filter === 'installed'     ? 'btn-outline-success'    :
                            b.dataset.filter === 'disabled'      ? 'btn-outline-secondary'  :
                                                                   'btn-outline-danger'
                        );
                    });

                    this.classList.add('active');
                    this.classList.remove(
                        'btn-outline-primary', 'btn-outline-success',
                        'btn-outline-secondary', 'btn-outline-danger'
                    );
                    this.classList.add(
                        filter === 'all'           ? 'btn-primary'   :
                        filter === 'installed'     ? 'btn-success'   :
                        filter === 'disabled'      ? 'btn-secondary' :
                                                     'btn-danger'
                    );

                    let visible = 0;
                    document.querySelectorAll('.js-module-card').forEach(card => {
                        const show = filter === 'all' || card.dataset.status === filter;
                        card.classList.toggle('d-none', !show);
                        if (show) visible++;
                    });

                    document.getElementById('modules-empty').classList.toggle('d-none', visible > 0);
                });
            });
        </script>
    @endpush
@stop
