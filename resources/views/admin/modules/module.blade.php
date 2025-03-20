@extends('layout')

@section('title', __('admin.modules.module') . ' ' . $moduleConfig['name'])

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/modules">{{ __('index.modules') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin.modules.module') }} {{ $moduleConfig['name'] }}</li>
        </ol>
    </nav>
@stop

@section('header')
    @if ($module && ! $module['disabled'] && isset($moduleConfig['panel']))
        <div class="float-end">
            <a class="btn btn-success" href="{{ $moduleConfig['panel'] }}">{{ __('main.management') }}</a>
        </div>
    @endif

    <h1>{{ __('admin.modules.module') }} {{ $moduleConfig['name'] }}</h1>
@stop

@section('content')
    <div class="mb-3">
        {{ $moduleConfig['description'] }}
    </div>

    @if (isset($moduleConfig['info']))
        <div class="mb-3">
            {{ bbCode($moduleConfig['info']) }}<br>
        </div>
    @endif

    {{ __('main.version') }}: {{ $moduleConfig['version'] }}<br>
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

    @if (isset($moduleConfig['routes']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.routes') }}</div>
            <?= bbCode('[spoiler][code]' . $moduleConfig['routes'] . '[/code][/spoiler]') ?>
    @endif

    @if (isset($moduleConfig['hooks']))
        <div class="mt-2 fw-bold">{{ __('admin.modules.hooks') }}</div>
        <?= bbCode('[spoiler][code]' . $moduleConfig['hooks'] . '[/code][/spoiler]') ?>
    @endif

    <br>
    @if ($module)
        @if (version_compare($moduleConfig['version'], $module->version, '>'))
            <a class="btn btn-info" href="/admin/modules/install?module={{ $moduleName }}&amp;update=1">{{ __('main.refresh') }}</a>
        @endif

        @if ($module['disabled'])
            <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}&amp;enable=1">{{ __('main.enable') }}</a>
        @else
            <a class="btn btn-warning" href="/admin/modules/uninstall?module={{ $moduleName }}&amp;disable=1">{{ __('main.disable') }}</a>
        @endif

        <a class="btn btn-danger" href="/admin/modules/uninstall?module={{ $moduleName }}" onclick="return confirm('{{ __('admin.modules.confirm_delete') }}')">{{ __('main.delete') }}</a>

        @if (isset($moduleConfig['migrations']))
            <div class="text-muted fst-italic my-3">{{ __('admin.modules.hint') }}</div>
        @endif
    @else
        <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}">{{ __('main.install') }}</a>
    @endif
@stop
