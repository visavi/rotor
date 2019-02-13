@extends('layout')

@section('title')
    Модуль {{ $module['name'] }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/modules">Модули</a></li>
            <li class="breadcrumb-item active">Модуль {{ $module['name'] }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ $module['name'] }}<br>
    {{ $module['description'] }}<br>
    Версия: {{ $module['version'] }}<br>
    Автор: {{ $module['author'] }} <a href="{{ $module['homepage'] }}">{{ $module['homepage'] }}</a><br>

    @if (isset($module['screenshots']))
        <?php $countScreens = count($module['screenshots']); ?>
        <div id="myCarousel" class="carousel slide" data-ride="carousel">
            @if ($countScreens > 1)
                <ol class="carousel-indicators">
                    @for ($i = 0; $i < $countScreens; $i++)
                        <li data-target="#myCarousel" data-slide-to="{{ $i }}"{!! empty($i) ? ' class="active"' : '' !!}></li>
                    @endfor
                </ol>
            @endif

            <div class="carousel-inner">
                @foreach ($module['screenshots'] as $key => $screenshot)
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

    @if (isset($module['migrations']))
        <div class="mt-2 font-weight-bold">Список миграций</div>
        @foreach ($module['migrations'] as $migration)
            <i class="fas fa-database"></i> {{ $migration }}<br>
        @endforeach
    @endif


    @if (isset($module['symlinks']))
        <div class="mt-2 font-weight-bold">Список симлинков</div>
        @foreach ($module['symlinks'] as $key => $symlink)
            <i class="fas fa-images"></i> {{ $key }} -> {{ $symlink }}<br>
        @endforeach
    @endif

    <br>
    @if ($moduleActive)
        @if (version_compare($module['version'], $moduleActive->version, '>'))
            <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}">Обновить модуль</a>
        @endif
        <a class="btn btn-danger" href="/admin/modules/uninstall?module={{ $moduleName }}" onclick="return confirm('Вы действительно хотите отключить модуль?')">Отключить модуль</a>

        @if (isset($module['migrations']))
            <p class="text-muted font-italic">Внимание! При отключении модуля, будут удалены изменение в БД</p>
        @endif
    @else
        <a class="btn btn-success" href="/admin/modules/install?module={{ $moduleName }}">Включить модуль</a>
    @endif
@stop
