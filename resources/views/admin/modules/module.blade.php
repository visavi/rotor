@extends('layout')

@section('title')
    {{ __('admin.modules.module') }} {{ $moduleConfig['name'] }}
@stop

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
        <div class="float-right">
            <a class="btn btn-success" href="{{ $moduleConfig['panel'] }}">{{ __('main.management') }}</a>
        </div><br>
    @endif

    <h1>{{ __('admin.modules.module') }} {{ $moduleConfig['name'] }}</h1>
@stop

@section('content')
    {{ $moduleConfig['description'] }}<br>
    {{ __('main.version') }}: {{ $moduleConfig['version'] }}<br>
    {{ __('main.author') }}: {{ $moduleConfig['author'] }} <a href="{{ $moduleConfig['homepage'] }}">{{ $moduleConfig['homepage'] }}</a><br>

    @if (isset($moduleConfig['screenshots']))
        <?php $countScreens = count($moduleConfig['screenshots']); ?>
        <div id="myCarousel" class="carousel slide media-file my-3" data-ride="carousel">
            @if ($countScreens > 1)
                <ol class="carousel-indicators">
                    @for ($i = 0; $i < $countScreens; $i++)
                        <li data-target="#myCarousel" data-slide-to="{{ $i }}"{!! empty($i) ? ' class="active"' : '' !!}></li>
                    @endfor
                </ol>
            @endif

            <div class="carousel-inner">
                @foreach ($moduleConfig['screenshots'] as $key => $screenshot)
                    <div class="carousel-item{{ empty($key) ? ' active' : '' }}">
                        {!! imageBase64($screenshot, ['class' => 'd-block w-100']) !!}
                    </div>
                @endforeach
            </div>

            @if ($countScreens > 1)
                <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            @endif
        </div>
    @endif

    @if (isset($moduleConfig['migrations']))
        <div class="mt-2 font-weight-bold">{{ __('admin.modules.migrations') }}</div>
        @foreach ($moduleConfig['migrations'] as $migration)
            <i class="fas fa-database"></i> {{ $migration }}<br>
        @endforeach
    @endif

    @if (isset($moduleConfig['symlink']))
        <div class="mt-2 font-weight-bold">{{ __('admin.modules.symlink') }}</div>
        <i class="fas fa-external-link-alt"></i> {{ $moduleConfig['symlink'] }}<br>
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
            <p class="text-muted font-italic">{{ __('admin.modules.hint') }}</p>
        @endif
    @else
        <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}">{{ __('main.install') }}</a>
    @endif
@stop
