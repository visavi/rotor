@extends('layout')

@section('title', __('loads.edit_down'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">{{ __('index.loads') }}</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('loads.edit_down') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> {{ __('loads.pending_down1') }}<br>
        {{ __('loads.pending_down2') }}
    </div>

    <div class="section-form mb-3 shadow">
        @include('loads/_form')
    </div>
@stop
