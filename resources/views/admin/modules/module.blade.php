@extends('layout')

@section('title', __('admin.modules.module') . ' ' . $moduleConfig['name'])

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/modules">{{ __('index.modules') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.modules.module') }} {{ $moduleConfig['name'] }}</li>
        </ol>
    </nav>
@stop

@section('header')
    @if ($module && $module->active && isset($moduleConfig['panel']))
        @php
            $links = is_string($moduleConfig['panel'])
                ? [$moduleConfig['panel'] => __('main.management')]
                : $moduleConfig['panel'];
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

    <h1>{{ __('admin.modules.module') }} {{ $moduleConfig['name'] }}</h1>
@stop

@section('content')
    <div class="mb-3">
        {{ $moduleConfig['description'] }}
    </div>

    @if (isset($moduleConfig['info']))
        <div class="mb-3">
            {{ bbCode($moduleConfig['info']) }}
        </div>
    @endif

    {{ __('main.version') }}: {{ $module->version ?? $moduleConfig['version']  }}<br>
    {{ __('main.author') }}: {{ $moduleConfig['author'] }} <a href="{{ $moduleConfig['homepage'] }}">{{ $moduleConfig['homepage'] }}</a><br>

    @if (isset($moduleConfig['screenshots']))
        <?php $countScreens = count($moduleConfig['screenshots']); ?>
        <div id="myCarousel" class="carousel slide media-file my-3" data-bs-ride="carousel">
            @if ($countScreens > 1)
                <div class="carousel-indicators">
                    @for ($i = 0; $i < $countScreens; $i++)
                        <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="{{ $i }}"{!! empty($i) ? ' class="active"' : '' !!}></button>
                    @endfor
                </div>
            @endif

            <div class="carousel-inner">
                @foreach ($moduleConfig['screenshots'] as $screenshot)
                    <div class="carousel-item{{ $loop->first ? ' active' : '' }}">
                        {{ imageBase64($screenshot, ['class' => 'w-100']) }}
                    </div>
                @endforeach
            </div>

            @if ($countScreens > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            @endif
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
            <?= bbCode('[spoiler][code]' . $moduleConfig['config'] . '[/code][/spoiler]') ?>
    @endif

    @if (isset($moduleConfig['settings']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.settings') }}</div>
            <?= bbCode('[spoiler][code]' . $moduleConfig['settings'] . '[/code][/spoiler]') ?>
    @endif

    @if (isset($moduleConfig['routes']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.routes') }}</div>
            <?= bbCode('[spoiler][code]' . $moduleConfig['routes'] . '[/code][/spoiler]') ?>
    @endif

    @if (isset($moduleConfig['hooks']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.hooks') }}</div>
        <?= bbCode('[spoiler][code]' . $moduleConfig['hooks'] . '[/code][/spoiler]') ?>
    @endif

    @if (isset($moduleConfig['middleware']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.middleware') }}</div>
        <?= bbCode('[spoiler][code]' . $moduleConfig['middleware'] . '[/code][/spoiler]') ?>
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
    @endif
@stop
