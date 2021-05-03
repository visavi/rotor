@extends('layout')

@section('title', __('errors.error'))

@section('header', '')
@section('description', __('errors.error'))

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-12 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error">
        </div>
        <div class="col-12 text-center">
            <h1>{{ __('errors.error') }}!</h1>

            <div class="lead">{{ $message }}</div>

            @if ($referer)
                <div class="my-3">
                    <a class="btn btn-primary" href="{{ $referer }}"><i class="fa fa-arrow-circle-left"></i> {{ __('errors.return') }}</a>
                </div>
            @endif
        </div>
    </div>
@stop
