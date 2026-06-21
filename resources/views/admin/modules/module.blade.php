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

    @if ($hasUpdate && ! empty($registryInfo['changelog']))
        <details class="spoiler mt-2">
            <summary class="text-info"><i class="fa fa-list"></i> {{ __('admin.modules.changelog') }} {{ $registryVersion }}</summary>
            <div class="small mt-1">{!! nl2br(e($registryInfo['changelog'])) !!}</div>
        </details>
    @endif

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

    @if (! empty($moduleConfig['changelog']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.changelog_full') }}</div>
        <details class="spoiler">
            <summary>{{ __('main.expand_view') }}</summary>
            @php
                $cl = preg_replace('/^#{1,6}\s*(.+)$/m', '<strong>$1</strong>', e($moduleConfig['changelog']));
            @endphp
            <div class="small mt-1">{!! nl2br($cl) !!}</div>
        </details>
    @endif

    @if (isset($moduleConfig['migrations']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.migrations') }}</div>
        @foreach ($moduleConfig['migrations'] as $name => $migration)
            <details class="spoiler">
                <summary><i class="fas fa-database"></i> {{ $name }}</summary>
                <pre class="code"><code>{{ $migration }}</code></pre>
            </details>
        @endforeach
    @endif

    @if (isset($moduleConfig['symlink']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.symlink') }}</div>
        <i class="fas fa-external-link-alt"></i> {{ $moduleConfig['symlink'] }}<br>
    @endif

    @if (! empty($moduleConfig['publish']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.publish') }}</div>
        @foreach ($moduleConfig['publish'] as $from => $to)
            <i class="fas fa-copy"></i> {{ $from }} <i class="fas fa-arrow-right"></i> {{ $to }}<br>
        @endforeach
    @endif

    @if (isset($moduleConfig['config']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.config') }}</div>
        <details class="spoiler">
            <summary>{{ __('main.expand_view') }}</summary>
            <pre class="code"><code>{{ $moduleConfig['config'] }}</code></pre>
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
        <div class="d-flex flex-wrap gap-2">
            @if (version_compare($moduleConfig['version'], $module->version, '>'))
                <form action="{{ route('admin.modules.install') }}" method="post">
                    @csrf
                    <input type="hidden" name="module" value="{{ $moduleName }}">
                    <input type="hidden" name="update" value="1">
                    <button class="btn btn-info"><i class="fas fa-arrow-up"></i> {{ __('admin.modules.update_apply') }}</button>
                </form>
            @endif

            @if ($module['active'])
                <form action="{{ route('admin.modules.uninstall') }}" method="post">
                    @csrf
                    <input type="hidden" name="module" value="{{ $moduleName }}">
                    <input type="hidden" name="disable" value="1">
                    <button class="btn btn-warning">{{ __('main.disable') }}</button>
                </form>
            @else
                <form action="{{ route('admin.modules.install') }}" method="post">
                    @csrf
                    <input type="hidden" name="module" value="{{ $moduleName }}">
                    <input type="hidden" name="enable" value="1">
                    <button class="btn btn-success">{{ __('main.enable') }}</button>
                </form>
            @endif

            <form action="{{ route('admin.modules.uninstall') }}" method="post"
                  onsubmit="return confirm('{{ __('admin.modules.confirm_delete') }}')">
                @csrf
                <input type="hidden" name="module" value="{{ $moduleName }}">
                <button class="btn btn-danger">{{ __('main.delete') }}</button>
            </form>
        </div>

        @if (isset($moduleConfig['migrations']))
            <div class="text-muted fst-italic my-3">{{ __('admin.modules.hint') }}</div>
        @endif
    @else
        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('admin.modules.install') }}" method="post">
                @csrf
                <input type="hidden" name="module" value="{{ $moduleName }}">
                <button class="btn btn-success">{{ __('main.install') }}</button>
            </form>
            <form action="{{ route('admin.modules.delete') }}" method="post"
                  onsubmit="return confirm('{{ __('admin.modules.confirm_delete_files') }}')">
                @csrf
                <input type="hidden" name="module" value="{{ $moduleName }}">
                <button class="btn btn-danger">{{ __('admin.modules.delete_files') }}</button>
            </form>
        </div>
    @endif
@stop
