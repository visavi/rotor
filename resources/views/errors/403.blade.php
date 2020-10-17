@extends('layout')

@section('title', __('errors.error') . ' 403')

@section('header', '')
@section('description', __('errors.error') . ' 403')

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error 403">
        </div>
        <div class="col-md-8 text-center">
            <h1>{{ __('errors.error') }} 403!</h1>

            @if ($message)
                <div class="lead">{{ $message }}</div>
            @else
                <div class="lead">{{ __('errors.forbidden') }}</div>
            @endif

            @if ($referer)
                <div class="m-3">
                    <i class="fa fa-arrow-circle-left"></i> <a href="{{ $referer }}">{{ __('errors.return') }}</a><br>
                </div>
            @endif
        </div>
    </div>
@stop
