@extends('layout')

@section('title')
    {{ trans('main.site_rules') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('main.site_rules') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($rules)
        {!! bbCode($rules['text']) !!}<br>
    @else
        {!! showError(trans('pages.empty_rules')) !!}
    @endif
@stop
