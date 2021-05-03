@extends('layout')

@section('title', __('errors.error') . ' 405')

@section('header', '')
@section('description', __('errors.error') . ' 405')

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-12 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error 405">
        </div>
        <div class="col-12 text-center">
            <h1>{{ __('errors.error') }} 405!</h1>

            <div class="lead">{{ __('errors.method_not_allowed') }}</div>

            @if ($referer)
                <div class="my-3">
                    <a class="btn btn-primary" href="{{ $referer }}"><i class="fa fa-arrow-circle-left"></i> {{ __('errors.return') }}</a>
                </div>
            @endif
        </div>
    </div>
@stop
