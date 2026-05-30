@extends('layout')

@section('title', __('admin.modules.module') . ' ' . ($moduleConfig['name'] ?? $moduleName))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/modules">{{ __('index.modules') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.modules.module') }} {{ $moduleConfig['name'] ?? $moduleName }}</li>
        </ol>
    </nav>
@stop

@section('header')
    @if ($module && $module->active && isset($moduleConfig['actions']))
        @php
            $links = $moduleConfig['actions'];
        @endphp

        @if (count($links) === 1)
            <div class="float-end">
                <a class="btn btn-adaptive" href="{{ key($links) }}">{{ current($links) }}</a>
            </div>
        @else
            <div class="btn-group float-end">
                <button type="button" class="btn btn-adaptive dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ __('main.management') }}
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    @foreach ($links as $url => $label)
                        <a class="dropdown-item" href="{{ $url }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <h1>{{ __('admin.modules.module') }} {{ $moduleConfig['name'] ?? $moduleName }}</h1>
@stop

@section('content')
    <h3>{{ $moduleConfig['description'] }}</h3>

    @if (isset($moduleConfig['info']))
        <div class="mb-3">
            {{ renderHtml($moduleConfig['info']) }}
        </div>
    @endif

    <p>
        @php
            $installedVersion = $module->version ?? $moduleConfig['version'];
            $registryVersion  = $registryInfo['version'] ?? null;
            $hasUpdate        = $registryVersion && version_compare($registryVersion, $installedVersion, '>');
        @endphp
        {{ __('main.version') }}: {{ $installedVersion }}
        @if ($hasUpdate)
            <span class="badge bg-info ms-1">{{ __('main.update_available') }}: {{ $registryVersion }}</span>
        @endif
        <br>
        {{ __('main.author') }}: {{ $moduleConfig['author'] }} <a href="{{ $moduleConfig['homepage'] }}">{{ $moduleConfig['homepage'] }}</a><br>
        @if (! empty($moduleConfig['requires']))
            @php $compatible = version_compare(ROTOR_VERSION, $moduleConfig['requires'], '>='); @endphp
            <span class="{{ $compatible ? 'text-muted' : 'text-danger' }}">
                {{ __('admin.modules.requires') }}: Rotor >= {{ $moduleConfig['requires'] }}
            </span>
        @endif
    </p>

    @if (isset($moduleConfig['screenshots']))
        <div class="f-carousel media-file my-3" style="--f-carousel-slide-width: 100%; --f-carousel-spacing: 8px;">
            <div class="f-carousel__viewport">
                @foreach ($moduleConfig['screenshots'] as $screenshot)
                    @php $src = 'data:image/' . getExtension($screenshot) . ';base64,' . base64_encode(file_get_contents($screenshot)); @endphp
                    <div class="f-carousel__slide">
                        <a href="{{ $src }}" data-fancybox="module-screens">
                            <img src="{{ $src }}" alt="{{ basename($screenshot) }}" class="d-block w-100" style="max-height: 420px; object-fit: contain;">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (isset($moduleConfig['migrations']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.migrations') }}</div>
        @foreach ($moduleConfig['migrations'] as $migration)
            <i class="fas fa-database"></i> {{ $migration }}<br>
        @endforeach
    @endif

    @if (isset($moduleConfig['symlink']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.symlink') }}</div>
        <i class="fas fa-external-link-alt"></i> {{ $moduleConfig['symlink'] }}<br>
    @endif

    @if (isset($moduleConfig['config']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.config') }}</div>
        <details class="spoiler">
            <summary>{{ __('main.expand_view') }}</summary>
            <pre class="code"><code>{{ $moduleConfig['config'] }}</code></pre>
        </details>
    @endif

    @if (isset($moduleConfig['settings']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.settings') }}</div>
        <details class="spoiler">
            <summary>{{ __('main.expand_view') }}</summary>
            <pre class="code"><code>{{ $moduleConfig['settings'] }}</code></pre>
        </details>
    @endif

    @if (isset($moduleConfig['routes']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.routes') }}</div>
        <details class="spoiler">
            <summary>{{ __('main.expand_view') }}</summary>
            <pre class="code"><code>{{ $moduleConfig['routes'] }}</code></pre>
        </details>
    @endif

    @if (isset($moduleConfig['hooks']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.hooks') }}</div>
        <details class="spoiler">
            <summary>{{ __('main.expand_view') }}</summary>
            <pre class="code"><code>{{ $moduleConfig['hooks'] }}</code></pre>
        </details>
    @endif

    @if (isset($moduleConfig['helpers']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.helpers') }}</div>
        <details class="spoiler">
            <summary>{{ __('main.expand_view') }}</summary>
            <pre class="code"><code>{{ $moduleConfig['helpers'] }}</code></pre>
        </details>
    @endif

    @if (isset($moduleConfig['middleware']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.middleware') }}</div>
        <details class="spoiler">
            <summary>{{ __('main.expand_view') }}</summary>
            <pre class="code"><code>{{ $moduleConfig['middleware'] }}</code></pre>
        </details>
    @endif

    <br>
    @if ($module)
        @if (version_compare($moduleConfig['version'], $module->version, '>'))
            <a class="btn btn-info" href="/admin/modules/install?module={{ $moduleName }}&amp;update=1">{{ __('main.refresh') }}</a>
        @endif

        @if ($module['active'])
            <a class="btn btn-warning" href="/admin/modules/uninstall?module={{ $moduleName }}&amp;disable=1">{{ __('main.disable') }}</a>
        @else
            <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}&amp;enable=1">{{ __('main.enable') }}</a>
        @endif

        <a class="btn btn-danger" href="/admin/modules/uninstall?module={{ $moduleName }}" onclick="return confirm('{{ __('admin.modules.confirm_delete') }}')">{{ __('main.delete') }}</a>

        @if (isset($moduleConfig['migrations']))
            <div class="text-muted fst-italic my-3">{{ __('admin.modules.hint') }}</div>
        @endif
    @else
        <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}">{{ __('main.install') }}</a>
        <a class="btn btn-danger ms-2" href="/admin/modules/delete?module={{ $moduleName }}" onclick="return confirm('{{ __('admin.modules.confirm_delete_files') }}')">{{ __('admin.modules.delete_files') }}</a>
    @endif
@stop
