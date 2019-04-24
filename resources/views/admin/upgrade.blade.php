@extends('layout')

@section('title')
    {{ trans('index.upgrade') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.upgrade') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {!! nl2br($wrap->getMigrate()) !!}

    <br>
    <div class="alert alert-success">
        <i class="fa fa-check"></i> <b>База данных в актуальном состоянии</b>
    </div>
@stop
