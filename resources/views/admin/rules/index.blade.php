@extends('layout')

@section('title', __('index.site_rules'))

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/rules/edit">Редактировать</a>
    </div><br>

    <h1>{{ __('index.site_rules') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.site_rules') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($rules)
        <div>
            {!! bbCode($rules->text) !!}
            <hr>

            {{ __('main.date') }}: {{ dateFixed($rules->created_at) }}
        </div>
        <br>
    @else
        {!! showError(__('admin.rules.empty_rules')) !!}
    @endif
@stop
