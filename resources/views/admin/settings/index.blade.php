@extends('admin/settings/layout')

@section('title', __('index.site_settings'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.site_settings') }}</li>
        </ol>
    </nav>
@stop

@section('settings')
    @include('admin/settings/_' . $act)
@stop
