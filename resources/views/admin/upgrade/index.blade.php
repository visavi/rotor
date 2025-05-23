@extends('layout')

@section('title', __('index.upgrade'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.upgrade') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <h3>Версия сайта v{{ ROTOR_VERSION }}</h3>

    @if ($hasNewVersion)
        <div class="alert alert-warning my-3">
            <i class="fa fa-check"></i> <b>Доступно обновление!</b>
        </div>

        <div class="post mb-3">
            <div class="post-message fw-bold">
                <a href="{{ $latestRelease['html_url'] }}">{{ $latestRelease['name'] }}</a>
            </div>

            @if ($latestRelease['body'])
                <div class="post-message">
                    {{ $latestRelease['body'] }}
                </div>
            @endif

            <div class="post-author fw-light">
                <span class="avatar-micro">
                    <img class="avatar-default rounded-circle" src="{{ $latestRelease['author']['avatar_url'] }}" alt="Аватар">
                </span>

                <a href="{{ $latestRelease['author']['html_url'] }}">{{ $latestRelease['author']['login'] }}</a>
                <small class="post-date text-body-secondary fst-italic">{{ dateFixed(strtotime($latestRelease['created_at'])) }}</small>
            </div>

            <div>
                @if (isset($latestRelease['assets'][0]))
                    Скачать: <a href="{{ $latestRelease['assets'][0]['browser_download_url'] }}">{{ $latestRelease['assets'][0]['name'] }}</a> {{ formatSize($latestRelease['assets'][0]['size']) }}
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-success my-3">
            <i class="fa fa-check"></i> <b>У вас актуальная версия сайта</b>
        </div>
    @endif

    <div class="alert alert-success my-3">
        <i class="fa fa-check"></i> <b>База данных в актуальном состоянии</b>
    </div>

    <div class="section mb-3 shadow">
        <div class="fw-bold">Rotor by Vantuz - https://visavi.net v{{ ROTOR_VERSION }}</div>

        {!! nl2br($migrateOutput) !!}
    </div>
@stop
