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
    <div class="section mb-3 shadow">
        <div class="fw-bold">Rotor by Vantuz - https://visavi.net v{{ ROTOR_VERSION }}</div>

        {!! nl2br($output) !!}
    </div>

    <div class="alert alert-success">
        <i class="fa fa-check"></i> <b>База данных в актуальном состоянии</b>
    </div>
@stop
