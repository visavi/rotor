@extends('layout')

@section('title')
    {{ trans('errors.error') }}
@stop

@section('header', '')
@section('description', trans('errors.error'))
@section('keywords', '')

@section('content')

    <?php $images = glob(HOME.'/assets/img/errors/*.png'); ?>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="/assets/img/errors/{{ basename($images[array_rand($images)]) }}" alt="error">
        </div>
        <div class="col-md-8 text-center">
            <h1>{{ trans('errors.error') }}!</h1>

            <div class="lead">{{ $message }}</div>

            @if ($referer)
                <div class="m-3">
                    <i class="fa fa-arrow-circle-left"></i> <a href="{{ $referer }}">{{ trans('errors.return') }}</a><br>
                </div>
            @endif
        </div>
    </div>
@stop
