@extends('layout')

@section('title', __('main.site_rules'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('main.site_rules') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($rules)
        <div class="section mb-3 shadow">
            <div class="section-body section-message">{{ renderHtml($rules->text) }}</div>
        </div>
    @else
        {{ showError(__('pages.empty_rules')) }}
    @endif
@stop
