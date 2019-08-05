@extends('layout')

@section('title')
    {{ trans('admin.modules.module') }} {{ $moduleConfig['name'] }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/modules">{{ trans('index.modules') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('admin.modules.module') }} {{ $moduleConfig['name'] }}</li>
        </ol>
    </nav>
@stop

@section('header')
    @if ($module && ! $module['disabled'] && isset($moduleConfig['panel']))
        <div class="float-right">
            <a class="btn btn-success" href="{{ $moduleConfig['panel'] }}">{{ trans('main.management') }}</a>
        </div><br>
    @endif

    <h1>{{ trans('admin.modules.module') }} {{ $moduleConfig['name'] }}</h1>
@stop

@section('content')
    {{ $moduleConfig['description'] }}<br>
    {{ trans('main.version') }}: {{ $moduleConfig['version'] }}<br>
    {{ trans('main.author') }}: {{ $moduleConfig['author'] }} <a href="{{ $moduleConfig['homepage'] }}">{{ $moduleConfig['homepage'] }}</a><br>

    @if (isset($moduleConfig['screenshots']))
        <?php $countScreens = count($moduleConfig['screenshots']); ?>
        <div id="myCarousel" class="carousel slide" data-ride="carousel">
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
        <div class="mt-2 font-weight-bold">{{ trans('admin.modules.migrations') }}</div>
        @foreach ($moduleConfig['migrations'] as $migration)
            <i class="fas fa-database"></i> {{ $migration }}<br>
        @endforeach
    @endif


    @if (isset($moduleConfig['symlink']))
        <div class="mt-2 font-weight-bold">{{ trans('admin.modules.symlink') }}</div>
        <i class="fas fa-external-link-alt"></i> {{ $moduleConfig['symlink'] }}<br>
    @endif

    <br>
    @if ($module)
        @if (version_compare($moduleConfig['version'], $module->version, '>'))
            <a class="btn btn-info" href="/admin/modules/install?module={{ $moduleName }}&amp;update=1">{{ trans('main.refresh') }}</a>
        @endif

        @if ($module['disabled'])
            <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}&amp;enable=1">{{ trans('main.enable') }}</a>
        @else
            <a class="btn btn-warning" href="/admin/modules/uninstall?module={{ $moduleName }}&amp;disable=1">{{ trans('main.disable') }}</a>
        @endif

        <a class="btn btn-danger" href="/admin/modules/uninstall?module={{ $moduleName }}" onclick="return confirm('{{ trans('admin.modules.confirm_delete') }}')">{{ trans('main.delete') }}</a>

        @if (isset($moduleConfig['migrations']))
            <p class="text-muted font-italic">{{ trans('admin.modules.hint') }}</p>
        @endif
    @else
        <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}">{{ trans('main.install') }}</a>
    @endif
@stop
